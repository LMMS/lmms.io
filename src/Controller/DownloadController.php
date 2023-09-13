<?php
namespace App\Controller;

use LMMS\Artifacts;
use LMMS\Asset;
use LMMS\Platform;
use LMMS\Releases;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DownloadController extends AbstractController
{
    public function page(Releases $releases, Artifacts $artifacts): Response
    {
        try {
            $assets = $releases->latestStableAssets();
            $winstable = array_values(array_filter($assets, self::assetsForPlatform(Platform::Windows)));
            $osxstable = array_values(array_filter($assets, self::assetsForPlatform(Platform::MacOS)));
            $linstable = array_values(array_filter($assets, self::assetsForPlatform(Platform::Linux)));

            $assets = $releases->latestUnstableAssets();
            $winpre = array_values(array_filter($assets, self::assetsForPlatform(Platform::Windows)));
            $osxpre = array_values(array_filter($assets, self::assetsForPlatform(Platform::MacOS)));
            $linpre = array_values(array_filter($assets, self::assetsForPlatform(Platform::Linux)));

            if ($winstable && $winpre && ($winpre[0]->getDate() < $winstable[0]->getDate()))
                $winpre = null;
            if ($osxstable && $osxpre && ($osxpre[0]->getDate() < $osxstable[0]->getDate()))
                $osxpre = null;
            if ($linstable && $linpre && ($linpre[0]->getDate() < $linstable[0]->getDate()))
                $linpre = null;

            $assets = $artifacts->getForBranch('master');
            $winnightly = array_values(array_filter($assets, self::assetsForPlatform(Platform::Windows)));
            $osxnightly = array_values(array_filter($assets, self::assetsForPlatform(Platform::MacOS)));
            $linnightly = array_values(array_filter($assets, self::assetsForPlatform(Platform::Linux)));

            $vars = [
                'winstable' => $winstable,
                'winpre' => $winpre,
                'winnightly' => $winnightly,
                'osxstable' => $osxstable,
                'osxpre' => $osxpre,
                'osxnightly' => $osxnightly,
                'linstable' => $linstable,
                'linpre' => $linpre,
                'linnightly' => $linnightly
            ];
            return $this->render('download/index.twig', $vars);
        } catch (\Exception $e) {
            error_log($e);
            return $this->render('download/error.twig');
        }
    }

    public function pull_request(string $id, Artifacts $artifacts): Response
    {
        try {
            $assets = $artifacts->getForPullRequest($id);
            $winartifacts = array_values(array_filter($assets, self::assetsForPlatform(Platform::Windows)));
            $osxartifacts = array_values(array_filter($assets, self::assetsForPlatform(Platform::MacOS)));
            $linartifacts = array_values(array_filter($assets, self::assetsForPlatform(Platform::Linux)));

            $vars = [
                'id' => $id,
                'winartifacts' => $winartifacts,
                'osxartifacts' => $osxartifacts,
                'linartifacts' => $linartifacts
            ];
            return $this->render('download/pull-request.twig', $vars);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw $this->createNotFoundException(previous: $e);
            }
            error_log($e);
            return $this->render('download/error.twig');
        }
    }

    public function artifact(string $id, Artifacts $artifacts): Response
    {
        try {
            $response = new Response($artifacts->getArtifactBinary($id));
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $artifacts->getArtifactName($id)
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw $this->createNotFoundException(previous: $e);
            }
            error_log($e);
            return $this->render('download/error.twig');
        }
    }

    private static function assetsForPlatform(Platform $platform)
    {
        return function (Asset $asset) use ($platform) {
            return $asset->getPlatform() === $platform;
        };
    }
}
