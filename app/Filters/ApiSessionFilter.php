<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

/**
 * Filtro de autenticação por sessão do Shield voltado para APIs REST / SPA.
 * Retorna JSON com HTTP 401 caso não haja sessão ativa, em vez de
 * redirecionar (HTTP 302) para a página HTML de login.
 */
class ApiSessionFilter implements FilterInterface
{
    /**
     * Verifica se o usuário está autenticado na sessão do Shield.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if ($authenticator->loggedIn()) {
            if (setting('Auth.recordActiveDate')) {
                $authenticator->recordActiveDate();
            }

            $user = $authenticator->getUser();

            if ($user !== null && $user->isBanned()) {
                $error = $user->getBanMessage() ?? lang('Auth.logOutBannedUser');
                $authenticator->logout();

                return service('response')
                    ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => $error,
                    ]);
            }

            if ($user !== null && ! $user->isActivated()) {
                $authenticator->logout();

                return service('response')
                    ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => lang('Auth.activationBlocked'),
                    ]);
            }

            return;
        }

        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
            ->setJSON([
                'status'  => 'error',
                'message' => 'Acesso não autorizado. Sessão inexistente ou expirada.',
            ]);
    }

    /**
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
