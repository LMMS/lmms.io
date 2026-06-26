<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('lsp/login.twig', [
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    public function captcha(Request $request): Response
    {
        $code = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 5);
        $request->getSession()->set('captcha', $code);

        $font   = 5;
        $width  = imagefontwidth($font) * strlen($code) + 8;
        $height = imagefontheight($font) + 8;
        $image  = imagecreatetruecolor($width, $height);

        $bg   = imagecolorallocate($image, 255, 255, 255);
        $fg   = imagecolorallocate($image, 30, 30, 30);
        imagefill($image, 0, 0, $bg);
        imagestring($image, $font, 4, 4, $code, $fg);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();

        return new Response($png, 200, ['Content-Type' => 'image/png']);
    }
}
