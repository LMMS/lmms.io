<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Navbar\navbar;
use LMMS\Releases;

class DownloadController extends AbstractController
{
    public function page(Request $request)
    {
        try {
            $releases = new Releases();
    
            $winstable = [$releases->latestWin32Asset(), $releases->latestWin64Asset()];
            $winpre = [$releases->latestWin32Asset(false), $releases->latestWin64Asset(false)];
            $osxstable = $releases->latestOSXAssets();
            $osxpre = $releases->latestOSXAssets(false);
            $linstable = $releases->latestLinuxAssets();
            $linpre = $releases->latestLinuxAssets(false);
    
            if ($winstable[0] && $winpre[0] && ($winpre[0]['created_at'] < $winstable[0]['created_at']))
                $winpre = null;
            if ($osxstable && $osxpre && ($osxpre[0]['created_at'] < $osxstable[0]['created_at']))
                $osxpre = null;
            if ($linstable && $linpre && ($linpre[0]['created_at'] < $linstable[0]['created_at']))
                $linpre = null;
    
            $vars = [
                'winstable' => $winstable,
                'winpre' => $winpre,
                'osxstable' => $osxstable,
                'osxpre' => $osxpre,
                'linstable' => $linstable,
                'linpre' => $linpre
            ];
        } catch (Exception $e) {
            error_log($e);
            return $this->render('download/error.twig');
        }
    
        return $this->render('download/index.twig', $vars);
    }
}
