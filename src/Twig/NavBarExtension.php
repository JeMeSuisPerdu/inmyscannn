<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavBarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('navbar', [$this, 'renderNavbar'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function renderNavbar(\Twig\Environment $twig): string
    {
        return $twig->render('navbar/navbar.html.twig'); // Assurez-vous que le chemin est correct
    }
}
