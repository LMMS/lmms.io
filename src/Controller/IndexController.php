<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Navbar;
class IndexController extends AbstractController
{
    public function homepage()
    {
        // $this->get('twig')->addGlobal('navbar', $navbar);
        return $this->render('home.twig');
    }
}
