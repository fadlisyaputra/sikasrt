<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IotDevice;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     * Validates X-API-KEY or X-ESP32-KEY or device-specific api_key.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->header('X-ESP32-KEY') ?? $request->input('api_key');
        $masterKey = env('ESP32_API_KEY', 'sikasrt-esp32-secret-key-2026');

        // Master key matched
        if ($apiKey && $apiKey === $masterKey) {
            return $next($request);
        }

        // Check if device_id matches its registered api_key
        $deviceId = $request->input('device_id') ?? $request->header('X-DEVICE-ID');
        if ($deviceId) {
            $device = IotDevice::where('device_id', $deviceId)->first();
            if ($device) {
                // If device found and either apiKey matches device api_key OR no apiKey required in local
                if (!$apiKey || $device->api_key === $apiKey || $apiKey === $masterKey) {
                    return $next($request);
                }
            }
        }

        // In local development, if neither is provided but valid payload
        if (config('app.env') === 'local') {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'status' => 'unauthorized',
            'message' => 'Unauthorized: Invalid or missing API key.',
        ], 401);
    }
}
