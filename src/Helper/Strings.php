<?php

namespace Progeja\Bookmarks\Helper;

class Strings
{
    public static function toArray(string $content): array
    {
        return explode("\n", $content);
    }

    public static function toArrayTidy(string $content): array
    {
        $data = static::toArray($content);
        $data = array_map('trim', $data);
        $data = array_filter($data);

        return $data;
    }
}