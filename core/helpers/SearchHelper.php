<?php

namespace app\core\helpers;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\BaseType;

class SearchHelper
{
    /**
     * @param string $searchStr
     * @param $typeClassName
     * @return string
     * @throws InvalidArgumentException
     */
    public static function stringToInt(string $searchStr, $typeClassName)
    {
        $items = [];
        /** @var BaseType $type */
        $type = \Yii::createObject($typeClassName);
        $searchArr = explode(',', $searchStr);
        foreach ($searchArr as $search) {
            if (in_array($search, $type::names())) {
                $items[] = $type::toEnumValue($search);
            }
        }
        return implode(',', $items);
    }
}
