<?php

namespace App\Helpers;

use App\Models\Configuration;

class Config
{
    public static function getAppName(): ?string
    {
        return config('app.name');
    }
}