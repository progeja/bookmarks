<?php

namespace Progeja\Bookmarks\Helper;

class Files
{
    public static function get(string $fileName): string
    {
        if (!is_readable($fileName)) {
            throw new \RuntimeException("Cannot open file '$fileName'.");
        }

        return file_get_contents($fileName);
    }

    public static function getAsArray(string $fileName): array
    {
        $content = static::get($fileName);

        return Strings::toArrayTidy($content);
    }
}