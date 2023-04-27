<?php

if (!function_exists('naira_format')) {
    function naira_format($number, $decimals = 2, $decimalPoint = '.', $thousandsSeparator = ',')
    {
        return '₦'. number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
    }
}


if (!function_exists('number_format_short')) {
    function number_format_short($number)
    {
        return number_format((float) $number, 0, '.', ',');
    }
}



?>