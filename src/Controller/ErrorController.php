<?php 
// src/Controller/ErrorController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/404', name: 'error_404')]
    public function notFound(): Response
    {
        return $this->render('error/404.html.twig');
    }

    #[Route('/error', name: 'error')]
    public function showError(\Throwable $exception): Response
    {
        if ($exception instanceof NotFoundHttpException) {
            return $this->notFound();
        }

        // Vous pouvez gérer d'autres types d'erreurs ici si nécessaire
        return $this->render('error/notfound.html.twig', [
            'message' => 'Une erreur est survenue.',
        ]);
    }
}
