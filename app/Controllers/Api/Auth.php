<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;

class Auth extends BaseController
{
    /**
     * POST /api/auth/login
     * Autentica o usuário por sessão do Shield usando email/username e senha.
     */
    public function login(): ResponseInterface
    {
        $input = $this->getInput();

        $login    = trim((string) ($input['email'] ?? $input['username'] ?? $input['login'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $remember = (bool) ($input['remember'] ?? false);

        if ($login === '' || $password === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => 'Informe o e-mail ou apelido e a senha para autenticar.',
            ]);
        }

        // Determina se a credencial informada é e-mail ou apelido
        $credentials = ['password' => $password];
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $login;
        } else {
            $credentials['username'] = $login;
        }

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $result = $authenticator->remember($remember)->attempt($credentials);

        if (! $result->isOK()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => $result->reason() ?? 'Credenciais inválidas. Verifique os dados e tente novamente.',
            ]);
        }

        /** @var User $user */
        $user = $authenticator->getUser();

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Login realizado com sucesso.',
            'data'    => [
                'user'    => $this->formatUser($user),
                'isAdmin' => $user->can('games.manage'),
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Encerra a sessão ativa do Shield.
     */
    public function logout(): ResponseInterface
    {
        auth('session')->logout();

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Sessão encerrada com sucesso.',
        ]);
    }

    /**
     * GET /api/auth/me
     * Retorna os dados do usuário autenticado pela sessão atual.
     */
    public function me(): ResponseInterface
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if (! $authenticator->loggedIn()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Nenhuma sessão autenticada.',
            ]);
        }

        /** @var User $user */
        $user = $authenticator->getUser();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'user'    => $this->formatUser($user),
                'isAdmin' => $user->can('games.manage'),
            ],
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'username'    => $user->username,
            'email'       => $user->getEmail(),
            'name'        => $user->name ?? null,
            'avatar'      => $user->avatar ?? null,
            'birth_date'  => $user->birth_date !== null ? (string) $user->birth_date : null,
            'phone'       => $user->phone ?? null,
            'bio'         => $user->bio ?? null,
            'active'      => (bool) $user->active,
            'groups'      => $user->getGroups(),
            'permissions' => $user->getPermissions(),
        ];
    }

    private function getInput(): array
    {
        $body = (string) $this->request->getBody();
        if ($body !== '') {
            $json = json_decode($body, true);
            if (is_array($json)) {
                return $json;
            }
        }

        $post = $this->request->getPost();
        if (! empty($post)) {
            return $post;
        }

        return $this->request->getRawInput() ?? [];
    }
}
