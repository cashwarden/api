<?php

namespace app\core\helpers;

class ArrayHelper
{
    /**
     * @param $haystack
     * @param array|string $needle
     * @return false|int
     */
    public static function strPosArr($haystack, $needle)
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }
        foreach ($needle as $what) {
            if (($pos = strpos($haystack, $what)) !== false) {
                return $pos;
            }
        }
        return false;
    }
}
