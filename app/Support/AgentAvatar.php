<?php

namespace App\Support;

use App\Models\RealtorProfile;
use App\Models\User;

class AgentAvatar
{
    public const LOGO_PATH = 'images/omnireferral-logo.png';

    public const FALLBACK_SVG = 'images/about/about-omnireferral.svg';

    public const DEFAULT_PATH = self::LOGO_PATH;

    public static function url(?User $user = null, ?RealtorProfile $profile = null): string
    {
        $headshot = $profile?->headshot;
        if (is_string($headshot) && trim($headshot) !== '') {
            $resolved = self::resolvePath($headshot);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $avatar = $user?->profilePhotoPublicUrl();
        if ($avatar !== null) {
            return $avatar;
        }

        return self::logoUrl();
    }

    public static function logoUrl(): string
    {
        if (is_file(public_path(self::LOGO_PATH))) {
            return asset(self::LOGO_PATH);
        }

        return asset(self::FALLBACK_SVG);
    }

    public static function defaultPath(): string
    {
        return self::LOGO_PATH;
    }

    public static function defaultStorageHeadshot(): string
    {
        return self::LOGO_PATH;
    }

    private static function resolvePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $publicPath = public_path(ltrim($path, '/'));
        if (! is_file($publicPath)) {
            return null;
        }

        return asset(ltrim($path, '/'));
    }
}
