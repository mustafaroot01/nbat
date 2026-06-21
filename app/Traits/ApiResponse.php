<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'تمت العملية بنجاح', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function paginated($resource, $resourceClass = null, string $message = 'تم جلب البيانات')
    {
        $items = $resourceClass ? $resourceClass::collection($resource->items())->resolve() : $resource->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
            ],
        ]);
    }

    protected function error(string $message = 'حدث خطأ', int $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'data' => null,
        ], $code);
    }
}
