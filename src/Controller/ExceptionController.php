<?php
namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExceptionController extends AbstractController
{
    public function showError(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $code = $exception->getStatusCode();
        return new Response($this->renderView('errorpage.twig', [
            'code' => $code
        ]), $code);
    }
}
