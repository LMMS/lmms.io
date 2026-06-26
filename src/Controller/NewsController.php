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
			$nodes = $this->getNodes($graphql);
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		if (empty($nodes)) {
			return new RedirectResponse('https://github.com/LMMS/lmms/discussions/categories/announcements', 302);
		}

		return new RedirectResponse($this->generateUrl('news_show', ['date' => $this->dateOf($nodes[0])]), 302);
	}

	public function show(GraphQl $graphql, string $date): Response
	{
		try {
			$nodes = $this->getNodes($graphql);
		} catch (\Exception $e) {
			error_log($e);
			return $this->render('news/error.twig');
		}

		$dates = array_map(fn($n) => $this->dateOf($n), $nodes);
		$index = array_search($date, $dates, true);

		if ($index === false) {
			throw $this->createNotFoundException();
		}

		$formatted = $this->format($nodes[$index]);

		return $this->render('news/show.twig', [
			'item' => $formatted['html'],
			'title' => $formatted['title'],
			'date' => $date,
			'prev' => $index > 0 ? $dates[$index - 1] : null,
			'next' => $index < count($dates) - 1 ? $dates[$index + 1] : null,
		]);
	}

	private function getNodes(GraphQl $graphql): array
	{
		$results = $graphql->executeQuery($this->getQuery());
		return array_column($results['data']['repository']['discussions']['edges'], 'node');
	}

	private function dateOf(array $node): string
	{
		return (new \DateTime($node['createdAt']))->format('Y-m-d');
	}

	private function format(array $node): array
	{
		$timestamp = new \DateTime($node['createdAt']);
		$ts_formatted = $timestamp->format("F j, Y, g:i a");
		$ts_linked = "<a href='{$node['url']}'>$ts_formatted</a>";

		$hash = $timestamp->format('Y-m-d');
		$pattern = '/<h1([^>]*)>(.*?)<\/h1>/';
		$body = $node['bodyHTML'];
		$title = preg_match($pattern, $body, $m) ? trim(html_entity_decode(strip_tags($m[2]))) : $ts_formatted;
		$anchor = "<a href='#$hash' id='$hash'>";
		$html = preg_replace($pattern, '<h1$1>' . $anchor . '$2</a></h1>', $body, 1);

		$username = $node['author']['login'];
		$html .= "<small class='text-muted'>Published $ts_linked by <a href='//github.com/$username'>@$username</a></small>";
		$html = "<article lang='en' class='news-post' aria-labelledby='$hash'>$html</article>";

		return ['html' => $html, 'title' => $title];
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
