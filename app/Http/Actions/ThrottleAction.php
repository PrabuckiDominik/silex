<?php

declare(strict_types=1);

namespace App\Http\Actions;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

class ThrottleAction
{
    private const DURATIONS = [
        "15min" => 900,
        "1day" => 86400,
    ];

    public function handle(string $key, string $duration, ?string $translationKey = null): void
    {
        $seconds = Arr::get(self::DURATIONS, $duration);

        if ($seconds === null) {
            throw new \InvalidArgumentException("Invalid throttle duration: {$duration}");
        }

        $limiter = app(RateLimiter::class);

        if ($limiter->tooManyAttempts($key, 1)) {
            throw new HttpResponseException(response()->json([
                "message" => $translationKey ? __($translationKey) : __("Too many requests."),
            ], 429));
        }

        $limiter->hit($key, $seconds);
    }
}
