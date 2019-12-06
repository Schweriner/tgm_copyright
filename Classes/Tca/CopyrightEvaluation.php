<?php

namespace TGM\TgmCopyright\Tca;

class CopyrightEvaluation
{
    /**
     * This method returns js code
     *
     * @return string
     */
    public function returnFieldJS()
    {
        return "
            console.log(this);
            console.log(jQuery('[name$=\"[copyright]\"'));
        ";
    }

    /**
     * This method converts the value into dataType float
     * @param string $value
     * @param string $is_in
     * @param string $set
     * @return string
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        $floatValue = number_format((float)$value, 3);
        return $floatValue;
    }
}
