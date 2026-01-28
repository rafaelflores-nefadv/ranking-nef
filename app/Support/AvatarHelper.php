<?php

namespace App\Support;

use Illuminate\Support\Str;

class AvatarHelper
{
    public static function initials(string $name, int $max = 2): string
    {
        $name = trim($name);
        if ($name === '') {
            return '??';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $initials .= Str::upper(mb_substr($part, 0, 1));
        }

        $initials = mb_substr($initials, 0, $max);
        return $initials !== '' ? $initials : '??';
    }

    public static function dataUri(
        string $name,
        int $size = 128,
        string $background = '#6366f1',
        string $color = '#ffffff'
    ): string {
        $initials = self::initials($name);
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d">'
            . '<rect width="100%%" height="100%%" fill="%2$s"/>'
            . '<text x="50%%" y="50%%" dy=".35em" text-anchor="middle" fill="%3$s" '
            . 'font-family="Inter, Arial, sans-serif" font-size="%4$d" font-weight="600">%5$s</text>'
            . '</svg>',
            $size,
            $background,
            $color,
            (int) round($size * 0.42),
            e($initials)
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
