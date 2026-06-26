<?php
namespace App\Controller;

use LMMS\GraphQl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
	public function latest(GraphQl $graphql): Response
	{
		try {
			$items = $this->getItems($graphql);
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		if (empty($items)) {
			return new RedirectResponse('https://github.com/LMMS/lmms/discussions/categories/announcements', 302);
		}

		return new RedirectResponse($this->generateUrl('news_show', ['date' => $items[0]['date']]), 302);
	}

	public function show(GraphQl $graphql, string $date): Response
	{
		try {
			$items = $this->getItems($graphql);
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		$dates = array_column($items, 'date');
		$index = array_search($date, $dates, true);

		if ($index === false) {
			throw $this->createNotFoundException();
		}

		return $this->render('news/show.twig', [
			'item' => $items[$index]['html'],
			'title' => $items[$index]['title'],
			'date' => $date,
			'prev' => $index > 0 ? $items[$index - 1]['date'] : null,
			'next' => $index < count($items) - 1 ? $items[$index + 1]['date'] : null,
		]);
	}

	private function getItems(GraphQl $graphql): array
	{
		$results = $graphql->executeQuery($this->getQuery());
		$items = array();

		foreach ($results['data']['repository']['discussions']['edges'] as $result) {
			$url = $result['node']['url'];
			$timestamp = new \DateTime($result['node']['createdAt']);
			$username = $result['node']['author']['login'];
			$body = $result['node']['bodyHTML'];

			$ts_formatted = $timestamp->format("F j, Y, g:i a");
			$ts_linked = "<a href='$url'>$ts_formatted</a>";

			$hash = $timestamp->format('Y-m-d');
			$pattern = '/<h1([^>]*)>(.*?)<\/h1>/';
			$title = preg_match($pattern, $body, $m) ? trim(html_entity_decode(strip_tags($m[2]))) : $ts_formatted;
			$anchor = "<a href='#$hash' id='$hash'>";
			$anchored = preg_replace($pattern, '<h1$1>' . $anchor . '$2</a></h1>', $body, 1);

			$anchored .= "<small class='text-muted'>Published $ts_linked by <a href='//github.com/$username'>@$username</a></small>";
			$anchored = "<article lang='en' class='news-post' aria-labelledby='$hash'>$anchored</article>";

			$items[] = ['date' => $hash, 'html' => $anchored, 'title' => $title];
		}

		return $items;
	}

	private function getQuery(int $limit = 100): string
	{
		$announcementsCategory = "DIC_kwDOAPDEUM4CowUM";
		$first = "first: $limit,";

		return <<<GRAPHQL
		{
			repository(owner: "%owner%", name: "%repo%") {
				discussions(categoryId: "$announcementsCategory", $first orderBy: {field: CREATED_AT, direction: DESC}) {
					edges {
						node {
							author {
								login
							},
							url,
							createdAt,
							bodyHTML
						}
					}
				}
			}
		}
		GRAPHQL;
	}
}
