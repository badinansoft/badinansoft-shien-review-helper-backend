<?php

namespace App\Observers;

use App\Models\License;

class LicenseObserver
{
    public function creating(License $license): void
    {
        $license->key = License::generateKey();
    }
}
