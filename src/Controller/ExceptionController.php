<?php
namespace App\Controller;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExceptionController extends AbstractController
{
    public function showError(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $code = $exception->getStatusCode();
        $message = _('An unknown error occurred.');
        if ($code === 404) {
            $message = _('You seem to have taken a wrong turn.');
        }
        if ($code < 600 && $code >= 500) {
            $message = _('Oops, that is not supposed to happen.');
        }
        return new Response($this->renderView('errorpage.twig', [
            'message' => $message,
            'code' => $code
        ]), $code);
    }
}
