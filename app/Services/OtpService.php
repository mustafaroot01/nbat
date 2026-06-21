<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    // ── Config ──────────────────────────────────────────
    private function config(): array
    {
        return [
            'max_attempts' => (int) Setting::getValue('otp_max_attempts', 5),
            'cooldown'     => (int) Setting::getValue('otp_cooldown_seconds', 25),
            'expiry'       => (int) Setting::getValue('otp_expiry_minutes', 5),
            'api_key'      => Setting::getValue('otp_api_key', ''),
            'window'       => 15, // rate-limit window in minutes
        ];
    }

    public function isDisabled(): bool
    {
        return !Setting::getValue('feature_otp_verification', true);
    }

    // ── Send ────────────────────────────────────────────
    public function send(string $phone, ?string $ip = null): array
    {
        $config = $this->config();

        // Rate limit
        if ($this->isRateLimited($phone, $config)) {
            return $this->fail('لقد تجاوزت الحد المسموح. انتظر 15 دقيقة.', 429);
        }

        // Cooldown
        $cooldownRemaining = $this->getCooldownRemaining($phone, $config['cooldown']);
        if ($cooldownRemaining > 0) {
            return $this->fail(
                "انتظر {$cooldownRemaining} ثانية قبل إعادة الإرسال",
                429,
                ['cooldown_remaining' => $cooldownRemaining]
            );
        }

        // API key check
        if (empty($config['api_key'])) {
            return $this->fail('خدمة الرسائل غير مهيأة حالياً. تواصل مع المشرف.', 503);
        }

        // Generate & store
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpVerification::create([
            'phone'      => $phone,
            'code'       => $code,
            'expires_at' => now()->addMinutes($config['expiry']),
            'ip_address' => $ip,
        ]);

        // Send via OTPIQ
        if (!$this->dispatch($phone, $code, $config['api_key'])) {
            $otp->delete();

            return $this->fail('تعذر إرسال رمز التحقق. تأكد من رقم الهاتف أو حاول مجدداً.', 502);
        }

        // Debug log
        if (config('app.debug')) {
            Log::info("OTP [{$phone}]: {$code}");
        }

        return $this->ok('تم إرسال رمز التحقق', [
            'expires_in' => $config['expiry'] * 60,
            'cooldown'   => $config['cooldown'],
        ]);
    }

    // ── Verify ──────────────────────────────────────────
    public function verify(string $phone, string $code): array
    {
        $maxAttempts = (int) Setting::getValue('otp_max_attempts', 5);

        $otp = OtpVerification::where('phone', $phone)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$otp) {
            return $this->fail('لم يتم العثور على رمز تحقق لهذا الرقم', 404);
        }

        if ($otp->isExpired()) {
            return $this->fail('رمز التحقق منتهي الصلاحية. أعد الإرسال.', 410);
        }

        $otp->increment('attempts');

        if ($otp->attempts > $maxAttempts) {
            return $this->fail('تجاوزت عدد المحاولات المسموحة', 429);
        }

        if (!$otp->isValid($code)) {
            return $this->fail('رمز التحقق غير صحيح', 422, [
                'attempts_remaining' => max(0, $maxAttempts - $otp->attempts),
            ]);
        }

        $otp->update(['verified' => true]);

        return $this->ok('تم التحقق بنجاح', ['verified' => true]);
    }

    // ── Private helpers ─────────────────────────────────
    private function isRateLimited(string $phone, array $config): bool
    {
        return OtpVerification::where('phone', $phone)
            ->where('created_at', '>=', now()->subMinutes($config['window']))
            ->count() >= $config['max_attempts'];
    }

    private function getCooldownRemaining(string $phone, int $cooldown): int
    {
        $last = OtpVerification::where('phone', $phone)->latest()->first();

        if (!$last) {
            return 0;
        }

        $elapsed = $last->created_at->diffInSeconds(now());

        return $elapsed < $cooldown ? ($cooldown - $elapsed) : 0;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/^0/', '', trim($phone));

        if (!str_starts_with($phone, '964')) {
            $phone = '964' . $phone;
        }

        return ltrim($phone, '+');
    }

    private function dispatch(string $phone, string $code, string $apiKey): bool
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 300)
                ->withToken($apiKey)
                ->acceptJson()
                ->post('https://api.otpiq.com/api/sms', [
                    'phoneNumber'      => $this->normalizePhone($phone),
                    'smsType'          => 'verification',
                    'verificationCode' => $code,
                    'provider'         => 'whatsapp',
                ]);

            if (!$response->successful()) {
                Log::error("OTPIQ failed [{$response->status()}]: {$response->body()}");

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("OTPIQ exception: {$e->getMessage()}");

            return false;
        }
    }

    // ── Response builders ───────────────────────────────
    private function ok(string $message, array $data = []): array
    {
        return ['success' => true, 'message' => $message, 'data' => $data];
    }

    private function fail(string $message, int $code = 400, ?array $data = null): array
    {
        return ['success' => false, 'message' => $message, 'code' => $code, 'data' => $data];
    }
}
