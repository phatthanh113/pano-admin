<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const ALLOWED = ['en', 'vi', 'ja'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale')
            ?? $request->query('lang')
            ?? session('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);

        // lưu cookie để lần sau không cần session
        if (method_exists($response, 'withCookie')) {
            $response->withCookie(cookie()->forever('locale', $locale));
        }

        return $response;
    }
}
