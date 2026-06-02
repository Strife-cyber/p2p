<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($phone, '+')) {
            return '+'.$digits;
        }

        return '+'.$digits;
    }
}
