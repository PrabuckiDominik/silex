<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $acceptLanguage = $request->header("Accept-Language");

        $locale = config("app.locale");

        if ($acceptLanguage) {
            $languages = explode(",", $acceptLanguage);

            if (!empty($languages)) {
                $primaryLang = substr($languages[0], 0, 2);

                if (in_array($primaryLang, ["en", "pl"], true)) {
                    $locale = $primaryLang;
                }
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
