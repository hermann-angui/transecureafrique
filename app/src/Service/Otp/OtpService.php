<?php

namespace App\Service\Otp;

/**
 *
 */
class OtpService
{

    /**
     * @return string|null
     */
    public static function generate(int $len = 6) : ?string
    {
        if(empty($alphabet)) $alphabet = "123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len ; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }


    public static function checkValidity(string $code){

        return true;
    }

}
