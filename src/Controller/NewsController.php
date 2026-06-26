<?php
namespace App\Controller;

use App\News\NewsEntry;
use App\News\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
	public function latest(NewsRepository $news): Response
	{
		try {
			$entries = $news->findAll();
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		if (empty($entries)) {
			return new RedirectResponse('https://github.com/LMMS/lmms/discussions/categories/announcements', 302);
		}

		return new RedirectResponse($this->generateUrl('news_show', ['date' => $entries[0]->slug()]), 302);
	}

	public function show(NewsRepository $news, string $date): Response
	{
		try {
			$entries = $news->findAll();
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		$slugs = array_map(fn(NewsEntry $e) => $e->slug(), $entries);
		$index = array_search($date, $slugs, true);

		if ($index === false) {
			throw $this->createNotFoundException();
		}

		return $this->render('news/show.twig', [
			'entry' => $entries[$index],
			'prev' => $index > 0 ? $slugs[$index - 1] : null,
			'next' => $index < count($slugs) - 1 ? $slugs[$index + 1] : null,
		]);
	}
}
