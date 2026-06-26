<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractLspController extends AbstractController
{
    public function __construct(
        protected readonly CategoryRepository $categories,
    ) {}

    protected function categoryNav(string $category = '', string $subcategory = ''): array
    {
        return [
            'category_list' => $this->categories->getAll($category),
            'category'      => $category,
            'subcategory'   => $subcategory,
        ];
    }

    protected function errorResponse(string $message, int $status): Response
    {
        return $this->render('lsp/message.twig', [
            ...$this->categoryNav(),
            'titles'   => ['Error'],
            'severity' => 'danger',
            'icon'     => 'fa-exclamation-circle',
            'title'    => 'Error',
            'message'  => $message,
            'redirect' => '',
            'counter'  => 0,
        ], new Response('', $status));
    }
}
