<?php

namespace app\core\types;

class ColorType extends BaseType
{
    public const SKY = 'sky';
    public const GREEN = 'green';
    public const BLUE = 'blue';
    public const ORANGE = 'orange';
    public const RED = 'red';
    public const PURPLE = 'purple';
    public const PINK = 'pink';
    public const LIME = 'lime';
    public const GREY = 'grey';

    public static function names(): array
    {
        return [
            self::SKY => '#00AECC',
            self::GREEN => '#519839',
            self::BLUE => '#0079BF',
            self::ORANGE => '#D29034',
            self::RED => '#B04632',
            self::PURPLE => '#89609E',
            self::PINK => '#CD5A91',
            self::LIME => '#4BBF6B',
            self::GREY => '#838C91',
        ];
    }
}
