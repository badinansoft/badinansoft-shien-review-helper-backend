<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\License;
use App\Enums\LicenseStatus;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $licenseKey = $request->input('license_key');
        $deviceId = $request->input('device_id');

        if (!$licenseKey || !$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'License key and device ID are required'
            ], 401);
        }

        $license = License::where('key', $licenseKey)
            ->where('status', LicenseStatus::ACTIVE->value)
            ->where('device_id', $deviceId)
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive license'
            ], 401);
        }

        // Add license to the request for later use
        $request->merge(['license' => $license]);

        return $next($request);
    }
}
