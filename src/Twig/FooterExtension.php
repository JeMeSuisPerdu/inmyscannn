<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FooterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('footer', [$this, 'renderFooter'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function renderFooter(\Twig\Environment $twig): string
    {
        return $twig->render('navbar/footer.html.twig');
    }
}
