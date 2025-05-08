<?php
namespace App\Controller;

use LMMS\GraphQl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    public function page(GraphQl $graphql): Response
    {
        try {
            return $this->render('news/index.twig', [ 'news' => array_values($this->getHtml($graphql)) ]);
        } catch (\Exception $e) {
            error_log($e);
            return $this->render('news/error.twig');
        }
    }

    private function getHtml(GraphQl $graphql): array
    {
    	$results = $graphql->executeQuery($this->getQuery());
    	$html = array();
    	foreach ($results['data']['repository']['discussions']['edges'] as $result) {
    		$url = $result['node']['url'];
    		$timestamp = new \DateTime($result['node']['createdAt']);
    		$username = $result['node']['author']['login'];
    		$body = $result['node']['bodyHTML'];

		# Create link to Discussions thread
		$ts_formatted = $timestamp->format("F j, Y, g:i a");
    		$ts_linked = "<a href='$url'>$ts_formatted</a>";

    		# Create page anchor
    		$hash = $timestamp->format('Y-m-d');
    		$pattern = '/<h1([^>]*)>(.*?)<\/h1>/';
    		$anchor = "<a href='#$hash' id='$hash'>";
    		$anchored = preg_replace($pattern, '<h1$1>' . $anchor . '$2</a></h1>', $body, 1);

    		# Append timestamped footer with Discussions link
    		$anchored .= "<small class='text-muted'>Published $ts_linked by <a href='//github.com/$username'>@$username</a></small>";
			array_push($html, $anchored);
		}

		if(empty($html)) {
			array_push($html, "Monthly report not found. They are available on <a href='https://github.com/$this->owner/$this->repo/discussions/categories/announcements>GitHub discussions'</a>");
		}

		return $html;
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
