<?php

namespace framework\console;

enum ConsoleColors: int
{
    case AG_NORMAL = 0;
    case AG_BOLD = 1;
    case AG_UNDERLINE = 4;
    case AG_FLASH = 5;
    case AG_INVERT_TEXT_COLOR = 6;
    case AG_INVISIBLE = 7;

    case FG_BLACK = 30;
    case FG_RED = 31;
    case FG_GREEN = 32;
    case FG_YELLOW = 33;
    case FG_BLUE = 34;
    case FG_PURPLE = 35;
    case FG_CYAN = 36;
    case FG_WHITE = 37;

    case BG_BLACK = 40;
    case BG_RED = 41;
    case BG_GREEN = 42;
    case BG_YELLOW = 43;
    case BG_BLUE = 44;
    case BG_PURPLE = 45;
    case BG_CYAN = 46;
    case BG_WHITE = 47;
}
