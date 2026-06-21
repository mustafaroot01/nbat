<script setup lang="ts">
import { useSettingStore } from '@/stores/useSettingStore'

const settingStore = useSettingStore()
const loading = ref(true)
const savingGeneral = ref(false)
const savingMap = ref(false)
const savingFirebase = ref(false)
const savingOtp = ref(false)
const snackbar = ref(false)
const snackbarMsg = ref('')
const snackbarColor = ref('success')

const settings = ref({
  // General
  maintenance_mode: false,
  plant_approval_required: true,
  leaderboard_enabled: true,
  points_per_plant: 10,

  // Map
  map_provider: 'osm',
  google_maps_api_key: '',
  mapbox_api_key: '',

  // Firebase Client Config
  firebase_api_key: '',
  firebase_auth_domain: '',
  firebase_project_id: '',
  firebase_storage_bucket: '',
  firebase_messaging_sender_id: '',
  firebase_app_id: '',
  firebase_measurement_id: '',
  firebase_default_topic: 'all_users',

  // Firebase Server (Service Account)
  firebase_credentials: [] as File[],

  // OTP
  otp_api_key: '',
})

const mapProviders = [
  { title: 'Google Maps', value: 'google' },
  { title: 'Mapbox', value: 'mapbox' },
  { title: 'OpenStreetMap (مجاني)', value: 'osm' },
]

onMounted(() => {
  settingStore.fetchSettings().then(response => {
    const data = response.data.data
    settings.value = {
      ...settings.value,
      ...data,
      maintenance_mode: data.maintenance_mode === 'true' || data.maintenance_mode === true,
      plant_approval_required: data.plant_approval_required === 'true' || data.plant_approval_required === true,
      leaderboard_enabled: data.leaderboard_enabled === 'true' || data.leaderboard_enabled === true || data.leaderboard_enabled === undefined,
      points_per_plant: data.points_per_plant || 10,
      firebase_credentials: [],
    }
  }).finally(() => {
    loading.value = false
  })
})

const notify = (msg: string, color = 'success') => {
  snackbarMsg.value = msg
  snackbarColor.value = color
  snackbar.value = true
}

const saveSection = (ref: any, data: Record<string, any>) => {
  ref.value = true
  settingStore.updateSettings(data).then(() => {
    notify('تم الحفظ بنجاح ✅')
  }).catch(() => {
    notify('حدث خطأ أثناء الحفظ', 'error')
  }).finally(() => {
    ref.value = false
  })
}

const saveGeneral = () => saveSection(savingGeneral, {
  maintenance_mode: settings.value.maintenance_mode ? 'true' : 'false',
  plant_approval_required: settings.value.plant_approval_required ? 'true' : 'false',
  leaderboard_enabled: settings.value.leaderboard_enabled ? 'true' : 'false',
  points_per_plant: settings.value.points_per_plant,
})

const saveMap = () => saveSection(savingMap, {
  map_provider: settings.value.map_provider,
  google_maps_api_key: settings.value.google_maps_api_key,
  mapbox_api_key: settings.value.mapbox_api_key,
})

const saveFirebase = () => {
  savingFirebase.value = true

  const formData = new FormData()
  formData.append('firebase_api_key', settings.value.firebase_api_key)
  formData.append('firebase_auth_domain', settings.value.firebase_auth_domain)
  formData.append('firebase_project_id', settings.value.firebase_project_id)
  formData.append('firebase_storage_bucket', settings.value.firebase_storage_bucket)
  formData.append('firebase_messaging_sender_id', settings.value.firebase_messaging_sender_id)
  formData.append('firebase_app_id', settings.value.firebase_app_id)
  formData.append('firebase_measurement_id', settings.value.firebase_measurement_id)
  formData.append('firebase_default_topic', settings.value.firebase_default_topic)

  if (settings.value.firebase_credentials?.length > 0) {
    formData.append('firebase_credentials', settings.value.firebase_credentials[0])
  }

  settingStore.updateSettings(formData).then(() => {
    notify('تم حفظ إعدادات Firebase بنجاح ✅')
    settings.value.firebase_credentials = []
  }).catch(() => {
    notify('حدث خطأ أثناء الحفظ', 'error')
  }).finally(() => {
    savingFirebase.value = false
  })
}

const saveOtp = () => saveSection(savingOtp, {
  otp_api_key: settings.value.otp_api_key,
})
</script>

<template>
  <section>
    <VRow>
      <!-- ─── General Settings ─── -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem class="pb-4">
            <template #prepend>
              <VAvatar color="primary" variant="tonal" rounded>
                <VIcon icon="mdi-cog-outline" />
              </VAvatar>
            </template>
            <VCardTitle>إعدادات عامة</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pt-6">
            <VSwitch
              v-model="settings.maintenance_mode"
              label="وضع الصيانة"
              color="warning"
              class="mb-4"
            />
            <VSwitch
              v-model="settings.plant_approval_required"
              label="اعتماد النباتات يتطلب مراجعة من المشرف"
              color="primary"
              class="mb-4"
            />
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-3">نظام النقاط والمتصدرين</h6>
            <VSwitch
              v-model="settings.leaderboard_enabled"
              label="تفعيل لوحة المتصدرين في التطبيق"
              color="success"
              class="mb-4"
            />
            <VTextField
              v-model="settings.points_per_plant"
              label="عدد النقاط لكل نبتة مقبولة"
              type="number"
              min="1"
              hint="هذا الرقم سيُضرب في عدد نباتات المستخدم المقبولة"
              persistent-hint
            />
          </VCardText>
          <VDivider />
          <VCardActions class="pa-4 justify-end">
            <VBtn color="primary" variant="tonal" :loading="savingGeneral" prepend-icon="mdi-content-save-outline" @click="saveGeneral">
              حفظ
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- ─── Map Settings ─── -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem class="pb-4">
            <template #prepend>
              <VAvatar color="info" variant="tonal" rounded>
                <VIcon icon="mdi-map-marker-radius-outline" />
              </VAvatar>
            </template>
            <VCardTitle>إعدادات الخريطة</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pt-6">
            <VSelect
              v-model="settings.map_provider"
              label="مزود الخريطة"
              :items="mapProviders"
              class="mb-4"
            />
            <VTextField
              v-if="settings.map_provider === 'google'"
              v-model="settings.google_maps_api_key"
              label="Google Maps API Key"
              placeholder="AIzaSy..."
              class="mb-4"
            />
            <VTextField
              v-if="settings.map_provider === 'mapbox'"
              v-model="settings.mapbox_api_key"
              label="Mapbox Access Token"
              placeholder="pk.eyJ1..."
            />
          </VCardText>
          <VDivider />
          <VCardActions class="pa-4 justify-end">
            <VBtn color="info" variant="tonal" :loading="savingMap" prepend-icon="mdi-content-save-outline" @click="saveMap">
              حفظ
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- ─── Firebase Settings ─── -->
      <VCol cols="12">
        <VCard>
          <VCardItem class="pb-4">
            <template #prepend>
              <VAvatar color="warning" variant="tonal" rounded>
                <VIcon icon="mdi-firebase" />
              </VAvatar>
            </template>
            <VCardTitle>إعدادات Firebase</VCardTitle>
            <VCardSubtitle>إعدادات التطبيق (Client) وملف الصلاحيات (Server)</VCardSubtitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pt-6">
            <div class="text-caption text-medium-emphasis mb-4 d-flex align-center gap-2">
              <VIcon icon="mdi-cellphone" size="16" />
              إعدادات التطبيق (Client)
            </div>

            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_api_key"
                  label="API Key"
                  placeholder="AIzaSy..."
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_auth_domain"
                  label="Auth Domain"
                  placeholder="project-id.firebaseapp.com"
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_project_id"
                  label="Project ID"
                  placeholder="project-id"
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_storage_bucket"
                  label="Storage Bucket"
                  placeholder="project-id.firebasestorage.app"
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_messaging_sender_id"
                  label="Messaging Sender ID"
                  placeholder="225339402467"
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_app_id"
                  label="App ID"
                  placeholder="1:225339402467:web:85fbf18c..."
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_measurement_id"
                  label="Measurement ID (اختياري)"
                  placeholder="G-FQXEDE3GM9"
                  class="mb-2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="settings.firebase_default_topic"
                  label="Default Topic"
                  placeholder="all_users"
                  hint="يشترك به جميع المستخدمين"
                  persistent-hint
                  class="mb-2"
                />
              </VCol>
            </VRow>

            <VDivider class="my-6" />

            <div class="text-caption text-medium-emphasis mb-4 d-flex align-center gap-2">
              <VIcon icon="mdi-server-security" size="16" />
              ملف الصلاحيات (Service Account) — للسيرفر
            </div>

            <VFileInput
              v-model="settings.firebase_credentials"
              label="Service Account JSON File"
              accept=".json"
              prepend-icon="mdi-file-code-outline"
              show-size
              hint="ملف JSON من Firebase Console ← Project Settings ← Service Accounts ← Generate new private key"
              persistent-hint
            />
          </VCardText>
          <VDivider />
          <VCardActions class="pa-4 justify-end">
            <VBtn color="warning" variant="flat" :loading="savingFirebase" prepend-icon="mdi-firebase" @click="saveFirebase">
              حفظ إعدادات Firebase
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- ─── OTP Settings ─── -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem class="pb-4">
            <template #prepend>
              <VAvatar color="success" variant="tonal" rounded>
                <VIcon icon="mdi-message-processing-outline" />
              </VAvatar>
            </template>
            <VCardTitle>إعدادات رسائل التحقق (OTP)</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pt-6">
            <VTextField
              v-model="settings.otp_api_key"
              label="OTP API Key"
              placeholder="مفتاح مزود خدمة الرسائل النصية (OTPIQ)"
              class="mb-4"
            />
          </VCardText>
          <VDivider />
          <VCardActions class="pa-4 justify-end">
            <VBtn color="success" variant="tonal" :loading="savingOtp" prepend-icon="mdi-content-save-outline" @click="saveOtp">
              حفظ
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <VSnackbar v-model="snackbar" :timeout="3000" :color="snackbarColor">
      {{ snackbarMsg }}
    </VSnackbar>
  </section>
</template>

<route lang="yaml">
meta:
  action: read
  subject: settings
</route>
