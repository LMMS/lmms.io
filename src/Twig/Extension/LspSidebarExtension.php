<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Security\User;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class LspSidebarExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly Security $security) {}

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        $username = $user instanceof User ? $user->getUserIdentifier() : null;
        $isAdmin = $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles(), true);

        return [
            'username'      => $username,
            'is_admin'      => $isAdmin,
            'auth_failure'  => false,
            'commentsearch' => '',
        ];
    }
}
