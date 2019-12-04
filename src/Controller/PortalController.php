<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
        $path = $request->getPathInfo();
        return $this->render($pages[$path]);
    }
}
