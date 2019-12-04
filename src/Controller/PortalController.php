<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalController extends AbstractController
{
    public function page(Request $request)
    {
        $pages = [
            '/get-involved' => 'get-involved.twig',
            '/showcase' => 'showcase.twig',
            '/competitions' => 'competitions.twig',
            '/branding' => 'branding.twig'
        ];
        $content = null;
        $path = $request->getPathInfo();
        try {
            $content = $this->render($pages[$path]);
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('Page not found in PortalController');
        }
        return $content;
    }
}
