<?php

namespace FFDB\Instance\Filter;

class Operator
{
    public static function equal($specification,$statement){
        return $specification == $statement;
    }

    public static function notEqual($specification,$statement){
        return $specification != $statement;
    }

    public static function identical($specification,$statement){
        return $specification === $statement;
    }

    public static function notIdentical($specification,$statement){
        return $specification !== $statement;
    }

    public static function greater($specification,$statement){
        return $specification < $statement;
    }

    public static function greaterOrEqual($specification,$statement){
        return $specification <= $statement;
    }

    public static function less($specification,$statement){
        return $specification > $statement;
    }

    public static function lessOrEqual($specification,$statement){
        return $specification >= $statement;
    }

    public static function contains($specification,$statement){
        return in_array($statement,$specification);
    }

    public static function notContains($specification,$statement){
        return !in_array($statement,$specification);
    }

    public static function regex($specification,$statement){
        return preg_match($specification,$statement)?true:false;
    }
}