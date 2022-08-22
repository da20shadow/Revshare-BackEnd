<?php

namespace App\Services\validator;

use DateTime;

class InputValidator
{
    public static function validateStringInput($string): string
    {
        $string = trim($string);
        $string = htmlspecialchars($string);
        return stripcslashes($string);
    }

    public static function isDateValid($date): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);
        $date_errors = DateTime::getLastErrors();
        if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
            return false;
        }
        return true;
    }
}