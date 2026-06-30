<?php

declare(strict_types=1);

namespace App\Lsp\Account;

use Symfony\Component\HttpFoundation\Request;

final readonly class SettingsUpdate
{
    public function __construct(
        public string $realname,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            realname:             trim($request->request->getString('realname')),
            password:             $request->request->getString('password'),
            passwordConfirmation: $request->request->getString('password2'),
        );
    }
}
