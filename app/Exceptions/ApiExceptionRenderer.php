<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ApiExceptionRenderer
{
    public function __invoke(Throwable $exception, Request $request): ?Response
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], $exception->status);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Resource not found.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($exception instanceof HttpException) {
            $message = $exception->getMessage();

            return response()->json([
                'message' => $message !== '' && $message !== '0' ? $message : 'Request could not be completed.',
            ], $exception->getStatusCode());
        }

        report($exception);

        return response()->json([
            'message' => 'A server error occurred. Please try again later.',
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
