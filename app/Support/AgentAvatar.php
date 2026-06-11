<?php

namespace App\Support;

use App\Models\RealtorProfile;
use App\Models\User;

class AgentAvatar
{
    public const DEFAULT_PATH = 'assets/images/default-agent-avatar.svg';

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

        return asset(self::DEFAULT_PATH);
    }

    public static function defaultPath(): string
    {
        return self::DEFAULT_PATH;
    }

    public static function defaultStorageHeadshot(): string
    {
        return self::DEFAULT_PATH;
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

        return asset(ltrim($path, '/'));
    }
}
