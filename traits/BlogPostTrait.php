<?php

namespace AF\OCP5\Traits;

trait BlogPostTrait
{

    public static function checkTitle(string $title)
    {
        if (5 > strlen($title) || 255 < strlen($title)) {
            return false;
        }
        return true;
    }

    public static function checkCaption(string $caption)
    {
        if (5 > strlen($caption) || 255 < strlen($caption)) {
            return false;
        }
        return true;
    }

    public static function checkContent(string $content)
    {
        if (20 > strlen($content)) {
            return false;
        }
        return true;
    }
}