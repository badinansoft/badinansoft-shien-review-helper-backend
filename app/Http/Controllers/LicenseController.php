<?php

namespace App\Http\Controllers;

use App\Http\Requests\LicenseRequest;
use App\Http\Requests\LogUsageRequest;
use App\Http\Requests\VerifyLicenseRequest;
use App\Models\{License, Comment, Image, UsageLog};
use App\Enums\{LicenseStatus, ContentStatus};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Storage;

class LicenseController extends Controller
{
    public function verifyLicense(VerifyLicenseRequest $request)
    {

        // Find the license
        $license = License::where('key', $request->license_key)
                        ->where('status', LicenseStatus::ACTIVE->value)
                        ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive license key'
            ], 401);
        }

        // If the license is already assigned to a device
        if ($license->device_id && $license->device_id !== $request->device_id) {
            return response()->json([
                'success' => false,
                'message' => 'License is already in use on another device'
            ], 403);
        }

        // If the license is not yet assigned to a device, assign it
        if (!$license->device_id) {
            $license->device_id = $request->device_id;
            $license->save();
        }

        // Check if license has expired
        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            $license->status = LicenseStatus::EXPIRED;
            $license->save();

            return response()->json([
                'success' => false,
                'message' => 'License has expired'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'name' => $license->name,
            'expires_at' => $license->expires_at?->format('Y-m-d H:i:s'),
            'tokens_used' => $license->tokens_used
        ]);
    }


    public function getContent(VerifyLicenseRequest $request)
    {
        $license = $request->license;

        DB::beginTransaction();
        try {
            // Get 5 comments, prioritizing less-used ones first
            $comments = Comment::where('status', ContentStatus::ACTIVE->value)
                ->orderBy('used_count', 'asc')  // Sort by usage count, ascending
                ->limit(6)
                ->get(['id', 'text']);

            // Get 5 images, prioritizing less-used ones first
            $images = Image::where('status', ContentStatus::ACTIVE->value)
                ->orderBy('used_count', 'asc')  // Sort by usage count, ascending
                ->limit(6)
                ->get(['id', 'name', 'path', 'mime_type']);

            // Optimize: Update used_count for all comments in a single query using whereIn
            if ($comments->isNotEmpty()) {
                Comment::whereIn('id', $comments->pluck('id'))
                    ->update(['used_count' => DB::raw('used_count + 1')]);
            }

            // Optimize: Update used_count for all images in a single query using whereIn
            if ($images->isNotEmpty()) {
                Image::whereIn('id', $images->pluck('id'))
                    ->update(['used_count' => DB::raw('used_count + 1')]);
            }

            // Format image data
            $formattedImages = $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => route('image', $image->id),
                    'name' => $image->image_name,
                ];
            });

            // Increment tokens used for the license
            $license->tokens_used += 1;
            $license->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving content: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'images' => $formattedImages
        ]);
    }


    public function logUsage(LogUsageRequest $request)
    {
        // Verify license
        $license = $request->license;

        // Create usage log
        $usageLog = new UsageLog();
        $usageLog->license_id = $license->id;
        $usageLog->url = $request->url;
        $usageLog->timestamp = Carbon::now();
        $usageLog->reviews_filled = $request->reviews_filled;
        $usageLog->images_attached = $request->images_attached;
        $usageLog->user_agent = $request->header('User-Agent');
        $usageLog->ip_address = $request->ip();
        $usageLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Usage logged successfully'
        ]);
    }

    public function getLicenseStats(LicenseRequest $request)
    {
        // Find license
        $license = License::where('key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key'
            ], 401);
        }

        // Get usage statistics
        $totalReviewsFilled = $license->usageLogs()->sum('reviews_filled');
        $totalImagesAttached = $license->usageLogs()->sum('images_attached');
        $usageCount = $license->usageLogs()->count();
        $lastUsed = $license->usageLogs()->latest('timestamp')->first() ?
            $license->usageLogs()->latest('timestamp')->first()->timestamp : null;

        return response()->json([
            'success' => true,
            'license' => [
                'name' => $license->name,
                'key' => $license->key,
                'status' => $license->status->value,
                'device_id' => $license->device_id,
                'expires_at' => optional($license->expires_at)->format('Y-m-d H:i:s'),
                'tokens_used' => $license->tokens_used,
                'created_at' => $license->created_at->format('Y-m-d H:i:s')
            ],
            'usage' => [
                'total_requests' => $usageCount,
                'total_reviews_filled' => $totalReviewsFilled,
                'total_images_attached' => $totalImagesAttached,
                'last_used' => $lastUsed?->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function testApi()
    {
        return response()->json([
            'success' => true,
            'message' => 'API is working'
        ]);
    }

    public function getImage(Image $image)
    {
       $file = Storage::disk('public')->get($image->path);

       return response($file, 200)->header('Content-Type', $image->mime_type);
    }
}
