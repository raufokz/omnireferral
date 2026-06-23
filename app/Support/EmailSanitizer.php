<?php

namespace App\Support;

/**
 * Normalises sender/recipient email addresses into clean RFC 2822 form.
 *
 * Strips the formatting that causes Symfony Mailer "not a valid RFC 2822 address"
 * errors and SMTP "553 sender not authorized" rejections, e.g.:
 *   "<noreply@omnireferrals.com>"  → "noreply@omnireferrals.com"
 *   "[noreply@omnireferrals.com]"  → "noreply@omnireferrals.com"
 *   "mailto:noreply@omnireferrals.com" → "noreply@omnireferrals.com"
 *   "OmniReferral <noreply@omnireferrals.com>" → "noreply@omnireferrals.com"
 */
class EmailSanitizer
{
    /** Return a clean, validated email address, or null if it cannot be salvaged. */
    public static function address(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        // Drop a leading mailto: scheme.
        $value = preg_replace('/^\s*mailto:\s*/i', '', $value);

        // If wrapped as "Display Name <email>", pull out the address inside the brackets.
        if (preg_match('/<([^<>]+)>/', $value, $matches)) {
            $value = $matches[1];
        }

        // Strip any stray wrapping characters and whitespace.
        $value = trim($value, " \t\n\r\0\x0B<>[]\"'");

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }
}
