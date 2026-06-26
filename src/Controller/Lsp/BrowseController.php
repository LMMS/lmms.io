<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\CategoryRepository;
use App\Lsp\Legacy\LegacyRedirectResolver;
use App\Lsp\Project\ProjectRepository;
use App\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BrowseController extends AbstractLspController
{
    public function __construct(
        CategoryRepository $categories,
        private readonly ProjectRepository $projects,
        private readonly LegacyRedirectResolver $legacyRedirects,
    ) {
        parent::__construct($categories);
    }

    public function index(Request $request): Response
    {
        if (($redirect = $this->legacyRedirects->resolve($request)) !== null) {
            [$route, $params] = $redirect;
            return $this->redirectToRoute($route, $params, 301);
        }

        $action        = $request->query->get('action', '');
        $search        = $request->query->get('search', $request->query->get('q', ''));
        $category      = $request->query->get('category', '');
        $subcategory   = $request->query->get('subcategory', '');
        $user          = $request->query->get('user', '');
        $sort          = $request->query->get('sort', 'date');
        $order         = $request->query->get('order', 'DESC');
        $page          = (int) $request->query->get('page', 0);
        $commentSearch = (bool) $request->query->get('commentsearch', false);

        if ($search !== '') {
            [$count, $rows] = $this->projects->search(
                category: $category,
                subcategory: $subcategory,
                search: $search,
                sort: $sort,
                order: $order,
                page: $page,
                commentSearch: $commentSearch,
            );

            return $this->render('lsp/results_list.twig', [
                ...$this->categoryNav($category, $subcategory),
                'rows'   => $rows,
                'count'  => $count,
                'sort'   => $sort,
                'titles' => [$category, $subcategory, '"' . $search . '"'],
            ]);
        }

        if ($action === 'browse') {
            $titles = $user !== ''
                ? '(' . $user . ')'
                : [$category, $subcategory];

            [$count, $rows] = $this->projects->search(
                category: $category,
                subcategory: $subcategory,
                userName: $user,
                sort: $sort,
                order: $order,
                page: $page,
            );

            return $this->render('lsp/results_list.twig', [
                ...$this->categoryNav($category, $subcategory),
                'rows'   => $rows,
                'count'  => $count,
                'sort'   => $sort,
                'titles' => $titles,
            ]);
        }

        return $this->render('lsp/index.twig', [
            ...$this->categoryNav(),
            'rows' => $this->projects->getLatest(),
            'sort' => $sort,
        ]);
    }

    public function show(int $id): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $row = $this->projects->findById($id, $user?->getId());

        if ($row === null) {
            return $this->render('lsp/show_file.twig', [
                ...$this->categoryNav(),
                'rows' => [],
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        return $this->render('lsp/show_file.twig', [
            ...$this->categoryNav($row['category'], $row['subcategory']),
            'rows' => [$row],
        ]);
    }
}
