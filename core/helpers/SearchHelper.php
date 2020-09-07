<?php

namespace app\core\helpers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\types\BaseType;
use yii\base\InvalidConfigException;

class SearchHelper
{
    /**
     * @param string $searchStr
     * @param $typeClassName
     * @return string
     * @throws InvalidArgumentException
     * @throws InvalidConfigException|InternalException
     */
    public static function stringToInt(string $searchStr, $typeClassName)
    {
        $items = [];
        /** @var BaseType $type */
        $type = \Yii::createObject($typeClassName);
        if (!$type instanceof BaseType) {
            throw new InternalException('search string to Int fail');
        }
        $searchArr = explode(', ', $searchStr);
        foreach ($searchArr as $search) {
            if (in_array($search, $type::names())) {
                $items[] = $type::toEnumValue($search);
            }
        }
        return implode(', ', $items);
    }
}
