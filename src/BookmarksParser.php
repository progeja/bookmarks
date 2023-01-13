<?php

namespace Progeja\Bookmarks;

use ArrayObject;
use Progeja\Bookmarks\Enum\BookmarkLine;
use Progeja\Bookmarks\Helper\Strings;
use RuntimeException;

class BookmarksParser
{
    /**
     * Define line types to ignore in bookmarks file.
     *
     * @const array|BookmarkLine[]
     */
    private const LINES_TO_IGNORE = [
        BookmarkLine::UNKNOWN,
        BookmarkLine::COMMENT_START,
        BookmarkLine::COMMENT_END,
        BookmarkLine::COMMENT,
    ];

    /**
     * Bookmarks structure nodes stack of parent ids.
     *
     * @var array|int[]
     */
    private array $parentStack = [0];

    public function __construct()
    {
    }

    /**
     * Bookmarks file content parser.
     *
     * Bookmarks file content can be given as string or array of lines.
     *
     * @param \ArrayObject|array|string $bookmarksContent Bookmarks file content
     *
     * @return array Parsed bookmarks file structure array.
     */
    public function toArray(ArrayObject|array|string $bookmarksContent, bool $asTree = false): array
    {
        $bMarks = $this->inputToArrayObject($bookmarksContent);

        if (0 === $bMarks->count() || strtolower($bMarks[0]) !== '<!doctype netscape-bookmark-file-1>') {
            throw new RuntimeException('Not valid bookmarks file content.');
        }

        $bmIter = $bMarks->getIterator();
        $result = [];

        // loop through bookmarks file line by line
        while ($bmIter->valid()) {
            $bmLineNr = $bmIter->key();
            $bmLine   = $bmIter->current();
            $bmIter->next();
            $lineType = $this->lineType($bmLine);

            // Ignore some predefined line types. See class header
            if (in_array($lineType, static::LINES_TO_IGNORE, true)) {
                continue;
            }

            switch ($lineType) {
                case BookmarkLine::DOCTYPE:
                    $result['DOCTYPE'] = $bmLine;
                    break;
                case BookmarkLine::TITLE:
                    $result['TITLE'] = $this->stripTags($bmLine);
                    break;
                case BookmarkLine::META:
                    $result['META'][] = $this->getTagAttributes($bmLine, 'meta');
                    break;
                case BookmarkLine::HEADING:
                    $result['H'] = $this->stripTags($bmLine);
                    break;
                case BookmarkLine::LIST_HEADING:
                    $currentParent = $this->parentStack[0];
                    array_unshift($this->parentStack, $bmLineNr);

                    $result['LIST'][$bmLineNr] = [
                        'parent' => $currentParent,
                        'type'   => 'head',
                        'text'   => $this->stripTags($bmLine),
                        'attrib' => $this->getTagAttributes($bmLine, 'h3'),
                    ];

                    break;
                case BookmarkLine::LIST_ITEM:
                    $result['LIST'][$bmLineNr] = [
                        'parent' => $this->parentStack[0],
                        'type'   => 'item',
                        'text'   => $this->stripTags($bmLine),
                        'attrib' => $this->getTagAttributes($bmLine),
                    ];

                    break;
                case BookmarkLine::LIST_END:
                    array_shift($this->parentStack);
                    if (0 === count($this->parentStack)) {
                        $this->parentStack[] = 0;
                    }
                    break;
                default:
                    // do nothing
                    break;
            }
        }

        if ($asTree) {
            $result['LIST'] = $this->arrayFlatToTree($result['LIST']);
        }

        return $result;
    }

    public function arrayFlatToTree(array &$flatList, int $parent = 0): array
    {
        $result = [];
        foreach ($flatList as $id => $item) {
            if ($parent === $item['parent']) {
                if ($item['type'] === 'head') {
                    $item['sub'] = $this->arrayFlatToTree($flatList, $id);
                }
                $result[$id] = $item;
            }
        }

        return $result;
    }

    /**
     * Detect given line type.
     *
     * @param string $bmLine Bookmarks file line
     *
     * @return BookmarkLine
     */
    private function lineType(string $bmLine): BookmarkLine
    {
        $bmLine = strtolower(trim($bmLine));

        if (str_starts_with($bmLine, '<!doctype ')) {
            return BookmarkLine::DOCTYPE;
        }
        if (str_starts_with($bmLine, '<meta ')) {
            return BookmarkLine::META;
        }
        if (str_starts_with($bmLine, '<title>')) {
            return BookmarkLine::TITLE;
        }
        if (str_starts_with($bmLine, '<h1>')) {
            return BookmarkLine::HEADING;
        }
        if (str_starts_with($bmLine, '<dl><p>')) {
            return BookmarkLine::LIST_START;
        }
        if (str_starts_with($bmLine, '</dl><p>')) {
            return BookmarkLine::LIST_END;
        }
        if (str_starts_with($bmLine, '<dt><h3')) {
            return BookmarkLine::LIST_HEADING;
        }
        if (str_starts_with($bmLine, '<dt><a ')) {
            return BookmarkLine::LIST_ITEM;
        }
        if (str_contains($bmLine, '<!-- ')) {
            return BookmarkLine::COMMENT_START;
        }
        if (str_contains($bmLine, ' -->')) {
            return BookmarkLine::COMMENT_END;
        }

        return BookmarkLine::UNKNOWN;
    }

    private function inputToArrayObject(string|array|ArrayObject $inputVariable): ArrayObject
    {
        if (is_string($inputVariable)) {
            $inputVariable = Strings::toArrayTidy($inputVariable);
        }
        if (is_array($inputVariable)) {
            $inputVariable = new ArrayObject($inputVariable);
        }

        return $inputVariable;
    }

    /**
     * Remove all html/xml tags from line
     *
     * @param string $lineCode
     *
     * @return string
     */
    private function stripTags(string $lineCode): string
    {
        return strip_tags($lineCode);
    }

    /**
     * Get given tag attributes from current bookmarks line
     *
     * @param string $bmCodeLine
     * @param string $tag
     *
     * @return array
     */
    private function getTagAttributes(string $bmCodeLine, string $tag = 'a'): array
    {
        // Allowed symbols for tag (letters, numbers)
        if (!preg_match('/^[a-z0-9]+$/i', $tag)) { // not valid tag
            return [];
        }

        // Validate that line contains required tag.
        if (!preg_match('/<' . $tag . '[^>]*>/i', $bmCodeLine, $match)) { // line doesnt contain given tag
            return [];
        }

        // Get required tag arguments.
        preg_match_all('/([a-z_-]+)="([^"]*)"/i', $match[0], $result, PREG_PATTERN_ORDER);

        // Return assoc array of found tag attributes.
        return array_combine($result[1], $result[2]);
    }
}
