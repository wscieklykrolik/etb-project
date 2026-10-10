<?php

namespace App\Support;

use Illuminate\Support\Str;

class RichText
{
    private const ALLOWED_TAGS = '<p><div><br><strong><b><em><i><u><a><ul><ol><li>';

    public static function toHtml(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (! preg_match('/<(?:p|div|br|strong|b|em|i|u|a|ul|ol|li)\b/i', $value)) {
            return e($value);
        }

        return self::sanitize($value);
    }

    public static function forEditor(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (! preg_match('/<(?:p|div|br|strong|b|em|i|u|a|ul|ol|li)\b/i', $value)) {
            return nl2br(e($value));
        }

        return self::sanitize($value);
    }

    private static function sanitize(string $value): string
    {
        $value = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1\s*>/is', '', $value) ?? '';
        $value = strip_tags($value, self::ALLOWED_TAGS);

        return preg_replace_callback('/<\/?([a-z0-9]+)\b([^>]*)>/i', function (array $match): string {
            $tag = strtolower($match[1]);
            $closing = str_starts_with($match[0], '</');

            if ($closing) {
                return $tag === 'br' ? '' : "</{$tag}>";
            }

            if ($tag !== 'a') {
                return $tag === 'br' ? '<br>' : "<{$tag}>";
            }

            if (! preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $match[2], $hrefMatch)) {
                return '<a>';
            }

            $href = html_entity_decode($hrefMatch[1] ?: ($hrefMatch[2] ?: $hrefMatch[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $href = trim(preg_replace('/[\x00-\x20\x7f]+/', '', $href) ?? '');

            if (! self::isSafeUrl($href)) {
                return '<a>';
            }

            return '<a href="'.e($href).'" target="_blank" rel="noopener noreferrer">';
        }, $value) ?? '';
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (Str::startsWith($url, ['/', '#']) && ! Str::startsWith($url, '//')) {
            return true;
        }

        return (bool) preg_match('/^(https?:|mailto:|tel:)/i', $url);
    }
}
