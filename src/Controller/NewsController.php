<?php

namespace App\Controller;

use App\News\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    public function latest(NewsRepository $news): Response
    {
        try {
            $latest = $news->findLatest();
        } catch (\Exception $e) {
            error_log($e);
            $latest = null;
        }

        if ($latest === null) {
            return new RedirectResponse('https://github.com/LMMS/lmms/discussions/categories/announcements', 302);
        }

        return new RedirectResponse($this->generateUrl('news_show', ['date' => $latest->slug()]), 302);
    }

    public function show(NewsRepository $news, string $date): Response
    {
        try {
            $entry = $news->findOneByDate($date);
        } catch (\Exception $e) {
            error_log($e);
            return $this->render('news/error.twig');
        }

        if ($entry === null) {
            throw $this->createNotFoundException();
        }

        ['prev' => $prev, 'next' => $next] = $news->findNeighbors($date);

        return $this->render('news/show.twig', [
            'entry' => $entry,
            'prev'  => $prev?->slug(),
            'next'  => $next?->slug(),
        ]);
    }
}
