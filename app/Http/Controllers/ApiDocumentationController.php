<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiDocumentationController extends Controller
{
    public function ui(): View
    {
        return view('swagger', [
            'specUrl' => route('api.docs.spec'),
        ]);
    }

    public function spec(): Response
    {
        $path = storage_path('api-docs/openapi.yaml');

        abort_unless(is_readable($path), SymfonyResponse::HTTP_NOT_FOUND, 'OpenAPI specification not found.');

        $content = file_get_contents($path);
        $appUrl = rtrim(config('app.url'), '/');

        $content = str_replace(
            "default: http://p2p.test",
            "default: {$appUrl}",
            $content,
        );

        return response($content, SymfonyResponse::HTTP_OK, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}
