<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>|JsonResource|null  $data
     * @param  array<string, mixed>  $meta
     */
    public static function success(array|JsonResource|null $data, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'data' => $data instanceof JsonResource ? $data->resolve(request()) : $data,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, mixed>|JsonResource  $data
     * @param  array<string, mixed>  $meta
     */
    public static function created(array|JsonResource $data, array $meta = []): JsonResponse
    {
        return self::success($data, JsonResponse::HTTP_CREATED, $meta);
    }

    public static function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }

    /**
     * @param  array<string, list<string>>|null  $errors
     */
    public static function error(string $message, int $status, ?array $errors = null): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function resource(JsonResource $resource, int $status = 200): JsonResponse
    {
        return $resource->response()->setStatusCode($status);
    }

    public static function createdResource(JsonResource $resource, array $meta = []): JsonResponse
    {
        if ($meta === []) {
            return self::resource($resource, JsonResponse::HTTP_CREATED);
        }

        return response()->json([
            'data' => $resource->resolve(request()),
            'meta' => $meta,
        ], JsonResponse::HTTP_CREATED);
    }
}
