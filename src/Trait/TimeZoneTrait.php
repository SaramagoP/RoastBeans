<?php

namespace App\Trait;


    trait TimeZoneTrait
    {
        protected function changeTimeZone(mixed $timezoneId): void
        {
            \date_default_timezone_set($timezoneId); // Modifie le fuseau horaire par défaut en utilisant la fonction date_default_timezone_set()
        }
    }