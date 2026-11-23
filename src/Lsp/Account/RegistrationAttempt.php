<?php

declare(strict_types=1);

namespace App\Lsp\Account;

use Symfony\Component\HttpFoundation\Request;

final readonly class RegistrationAttempt
{
    public function __construct(
        public string $login,
        public string $realname,
        public string $password,
        public string $passwordConfirmation,
        public string $captcha,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            login:                trim($request->request->getString('login')),
            realname:             trim($request->request->getString('realname')),
            password:             $request->request->getString('password'),
            passwordConfirmation: $request->request->getString('password2'),
            captcha:              strtolower(trim($request->request->getString('captcha'))),
        );
    }
}
