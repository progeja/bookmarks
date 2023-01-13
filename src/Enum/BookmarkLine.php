<?php

namespace Progeja\Bookmarks\Enum;

enum BookmarkLine
{
    case UNKNOWN;
    case DOCTYPE;
    case COMMENT; // Currently not in use
    case COMMENT_START;
    case COMMENT_END;
    case META;
    case TITLE;
    case HEADING;
    case LIST_START;
    case LIST_HEADING;
    case LIST_ITEM;
    case LIST_END;
}
