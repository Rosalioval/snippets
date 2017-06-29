<?php

namespace App\Utils;

class CookieUtil
{
    const COOKIE_LENGTH = 32;

    const COOKIE_DURATION = 14;

    const DEFAULT_COOKIE_NAME = 'laravel_visitor_session';

    const COOKIE_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public $name;

    public $duration;
    
    public $cookieString;

    public function __construct(String $name = self::DEFAULT_COOKIE_NAME, Int $days = self::COOKIE_DURATION)
    {
        $this->name = $name;
        
        $this->duration = $this->getDuration($days);
        
        $this->cookieString = $this->getCookieString();
    }

    public function getCookieString(): string
    {
        // "ircmaxell/random-lib": "^1.2",
        $factory = new \RandomLib\Factory;
        
        $generator = $factory->getLowStrengthGenerator();
        
        $randomString = $generator->generateString(self::COOKIE_LENGTH, self::COOKIE_CHARACTERS);
        
        return $randomString;
    }

    public function getDuration(Int $days): int
    {
        $day = 86400;
        
        $duration = ($day * $days);

        return $duration;
    }
}