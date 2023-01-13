<?php

namespace Progeja\Bookmarks;

use Progeja\Bookmarks\Helper\Files;
use RuntimeException;

/**
 *
 */
class Bookmarks
{
    /**
     * @param string $bookmarksFileName
     * @param bool   $asTree
     *
     * @return array
     */
    public static function fileToArray(string $bookmarksFileName, bool $asTree = false): array
    {
        $bmParser = new BookmarksParser();
        try {
            $bmLines = Files::getAsArray($bookmarksFileName);
            $result  = $bmParser->toArray($bmLines, $asTree);
        } catch (RuntimeException $re) {
            die($re->getMessage());
        }

        return $result;
    }

    /**
     * @param string $bookmarksFileName
     * @param bool   $asTree
     *
     * @return string
     * @throws \JsonException
     */
    public static function fileToJson(string $bookmarksFileName, bool $asTree = false): string
    {
        $array = static::fileToArray($bookmarksFileName, $asTree);

        return json_encode($array, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $bookmarksContent
     *
     * @return array
     */
    public static function stringToArray(string $bookmarksContent): array
    {
        return [];
    }

    /**
     * @param string $bookmarksContent
     *
     * @return string
     */
    public static function stringToJson(string $bookmarksContent): string
    {
        return '{}';
    }

    /**
     * @param string $bookmarksArray
     * @param string $bookmarksFileName
     *
     * @return void
     */
    public static function arrayToFile(string $bookmarksArray, string $bookmarksFileName): void
    {
        return;
    }

    /**
     * @param string $bookmarksJson
     * @param string $bookmarksFileName
     *
     * @return void
     */
    public static function jsonToFile(string $bookmarksJson, string $bookmarksFileName): void
    {
        return;
    }

    /**
     * @param string $bookmarksArray
     *
     * @return string
     */
    public static function arrayToString(string $bookmarksArray): string
    {
        return '';
    }

    /**
     * @param string $bookmarksJson
     *
     * @return string
     */
    public static function jsonToString(string $bookmarksJson): string
    {
        return '';
    }
}