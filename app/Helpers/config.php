<?php

namespace App\Helpers;

use App\Models\Configuration;

class Config
{
    public static function getAppName(): ?string
    {
        
        $appName = config('app.name');
        return $appName ?: 'RiseTrack';
    }
}