<?php

namespace App\Support;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AgentAvatar
{
    public const LOGO_PATH = 'images/omnireferral-logo.png';

    public const FALLBACK_SVG = 'images/about/about-omnireferral.svg';

    public const DEFAULT_PATH = 'assets/images/default-agent-avatar.svg';

    /**
     * Canonical default image for public realtor/agent headshots.
     * Returned whenever realtor_profiles.headshot is null / empty / unresolvable.
     */
    public const DEFAULT_AGENT_IMAGE = 'images/realtors/logo-bydefault_agent.png';

    private const PLACEHOLDER_HEADSHOTS = [
        'images/realtors/10.png',
        'images/realtors/12.png',
        'images/realtors/14.png',
        'images/realtors/16.png',
        'images/realtors/18.png',
        'images/realtors/20.png',
        'images/realtors/22.png',
        'images/realtors/24.png',
        'images/realtors/26.png',
        'images/realtors/28.png',
        'images/realtors/30.png',
        'images/realtors/32.png',
    ];

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

        return self::placeholderUrl($user, $profile);
    }

    /**
     * Public realtor headshot resolver.
     *
     * Uses ONLY realtor_profiles.headshot as the source of truth (no users.avatar,
     * no random placeholder) so cards/modals never show the wrong person's photo.
     * Falls back to the canonical default agent image when headshot is missing.
     */
    public static function publicHeadshotUrl(?RealtorProfile $profile = null): string
    {
        $headshot = $profile?->headshot;

        if (is_string($headshot) && trim($headshot) !== '') {
            $resolved = self::resolveHeadshot(trim($headshot));
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return asset(self::DEFAULT_AGENT_IMAGE);
    }

    /**
     * Build a web URL from a stored headshot value. Handles full URLs, /storage and
     * storage/ web paths, bundled images/ or assets/ paths, and bare public-disk paths.
     * Genuinely broken files are caught by the frontend onerror fallback.
     */
    private static function resolveHeadshot(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        if (str_starts_with($path, '/images/') || str_starts_with($path, 'images/')
            || str_starts_with($path, '/assets/') || str_starts_with($path, 'assets/')) {
            return asset(ltrim($path, '/'));
        }

        // Bare path stored on the public disk (e.g. "avatars/photo.jpg").
        return Storage::disk('public')->url($path);
    }

    public static function logoUrl(): string
    {
        if (is_file(public_path(self::DEFAULT_PATH))) {
            return asset(self::DEFAULT_PATH);
        }

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
        return self::DEFAULT_PATH;
    }

    public static function placeholderUrl(?User $user = null, ?RealtorProfile $profile = null): string
    {
        $seed = (int) ($profile?->id ?? $user?->id ?? 0);
        $paths = array_values(array_filter(
            self::PLACEHOLDER_HEADSHOTS,
            fn (string $path): bool => is_file(public_path($path))
        ));

        if ($paths !== []) {
            return asset($paths[$seed % count($paths)]);
        }

        return self::logoUrl();
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
