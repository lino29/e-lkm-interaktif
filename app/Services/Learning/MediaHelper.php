<?php

namespace App\Services\Learning;

class MediaHelper
{
    /**
     * Extracts YouTube video ID and returns a safe embed URL.
     * Supports:
     * - youtube.com/watch?v=ID
     * - youtu.be/ID
     * - youtube.com/embed/ID
     */
    public static function getYoutubeEmbedUrl(?string $url): ?string
    {
        $videoId = self::getYoutubeVideoId($url);

        if (blank($videoId)) {
            return null;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    public static function getYoutubeVideoId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || ! isset($parsedUrl['host'])) {
            return null;
        }

        $host = strtolower($parsedUrl['host']);
        $videoId = null;

        if (str_contains($host, 'youtube.com')) {
            if (isset($parsedUrl['path']) && str_starts_with($parsedUrl['path'], '/embed/')) {
                // Already an embed URL, but let's re-format it to be safe
                $videoId = str_replace('/embed/', '', $parsedUrl['path']);
            } elseif (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $query);
                $videoId = $query['v'] ?? null;
            }
        } elseif (str_contains($host, 'youtu.be')) {
            if (isset($parsedUrl['path'])) {
                $videoId = ltrim($parsedUrl['path'], '/');
            }
        }

        if (blank($videoId)) {
            return null;
        }

        // Strip any trailing characters like ?t= or &feature= from video ID
        $videoId = explode('?', (string) $videoId)[0];
        $videoId = explode('&', $videoId)[0];

        return $videoId;
    }

    /**
     * Sanitizes an embed code.
     * In a real app, this should use HTMLPurifier allowing only specific iframe sources.
     * For now, we ensure it's at least wrapped in a generic container or return raw if trusted.
     */
    public static function sanitizeEmbedCode(?string $code): ?string
    {
        if (blank($code)) {
            return null;
        }

        // MVP: In this app context, Teachers (trusted users) input the embed code.
        // We will output it raw in the blade template using {!! !!} but we should
        // ideally strip script tags if any.
        $sanitized = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $code);

        return $sanitized;
    }
}
