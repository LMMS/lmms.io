<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\Account\AccountService;
use App\Lsp\Account\RegistrationAttempt;
use App\Lsp\Account\SettingsUpdate;
use App\Lsp\CategoryRepository;
use App\Lsp\UserRepository;
use App\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractLspController
{
    public function __construct(
        CategoryRepository $categories,
        private readonly AccountService $accounts,
        private readonly UserRepository $users,
    ) {
        parent::__construct($categories);
    }

    #[IsGranted('ROLE_USER')]
    public function settings(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $login = $user->getUserIdentifier();

        if ($request->request->get('settings') === 'apply') {
            $update = SettingsUpdate::fromRequest($request);
            $error  = $this->accounts->updateSettings($login, $update);

            if ($error !== null) {
                return $this->render('lsp/user_settings.twig', [
                    ...$this->categoryNav(),
                    'realname' => $update->realname,
                    'error'    => $error,
                ]);
            }

            return $this->render('lsp/message.twig', [
                ...$this->categoryNav(),
                'titles'   => ['User Settings'],
                'severity' => 'success',
                'icon'     => 'fa-check-circle',
                'title'    => 'Success',
                'message'  => 'Account settings have been updated.',
                'redirect' => '/lsp/settings',
                'counter'  => 3,
            ]);
        }

        return $this->render('lsp/user_settings.twig', [
            ...$this->categoryNav(),
            'realname' => $this->users->getRealname($login),
            'error'    => null,
        ]);
    }

    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $expectedCaptcha = (string) $request->getSession()->get('captcha', '');
            $request->getSession()->remove('captcha');

            $attempt = RegistrationAttempt::fromRequest($request);
            $error   = $this->accounts->register($attempt, $expectedCaptcha);

            if ($error !== null) {
                return $this->render('lsp/register.twig', [
                    ...$this->categoryNav(),
                    'error' => $error,
                ]);
            }

            return $this->render('lsp/message.twig', [
                ...$this->categoryNav(),
                'titles'   => ['Register'],
                'severity' => 'success',
                'icon'     => 'fa-check-circle',
                'title'    => 'Success',
                'message'  => '<strong>' . htmlspecialchars($attempt->login, ENT_QUOTES) . '</strong> has been successfully created. <a href=\'/lsp/login\'>Login here</a>.',
                'redirect' => '',
                'counter'  => 0,
            ]);
        }

        return $this->render('lsp/register.twig', [
            ...$this->categoryNav(),
            'error' => null,
        ]);
    }
}
