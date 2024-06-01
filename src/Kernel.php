<?php

namespace App;

use App\Trait\TimeZoneTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

class Kernel extends BaseKernel
{
    use MicroKernelTrait; // Utilisation du trait MicroKernelTrait
    use TimeZoneTrait; // Utilisation du trait TimeZoneTrait

    public function __construct(string $environement, bool $debug)
    {
        $this->changeTimeZone("Europe/Paris"); // Modifie le fuseau horaire par défaut pour "Europe/Paris"

        parent::__construct($environement, $debug); // Appelle le constructeur de la classe parente (BaseKernel)
    }
}
