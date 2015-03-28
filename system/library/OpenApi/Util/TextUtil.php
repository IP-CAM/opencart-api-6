<?php

namespace OpenApi\Util;

class TextUtil
{
    public static function makeCamelCase($text)
    {
        return str_replace(" ", "", ucwords(str_replace("-", " ", $text)));
    }
}