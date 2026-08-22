<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\GameService;
use CodeIgniter\HTTP\ResponseInterface;

class Game extends BaseController
{
    public function __construct(?GameService $service = null) {
        $this->service = $service ?? new GameService();
    }

    /**
     * GET /api/games
     * Catálogo público de jogos.
     */
    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $offset  = ($page - 1) * $perPage;

        $games = $this->service->findAll($perPage, $offset);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $games,
        ]);
    }

    /**
     * GET /api/games/(:num)
     */
    public function show(int $id)
    {
        $game = $this->service->findById($id);

        if ($game === null) {
            return $this->notFoundResponse();
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $game,
        ]);
    }

    /**
     * POST /api/games
     * Restrito a administradores (permissão `games.manage`).
     */
    public function create()
    {
        if (! $this->isAdmin()) {
            return $this->forbiddenResponse();
        }

        $result = $this->service->create($this->getInput());

        if ($result['status'] === 'error') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON($result);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON($result);
    }

    /**
     * PUT/PATCH /api/games/(:num)
     * Restrito a administradores (permissão `games.manage`).
     */
    public function update(int $id)
    {
        if (! $this->isAdmin()) {
            return $this->forbiddenResponse();
        }

        if ($this->service->findById($id) === null) {
            return $this->notFoundResponse();
        }

        $result = $this->service->update($id, $this->getInput());

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    /**
     * DELETE /api/games/(:num)
     * Restrito a administradores (permissão `games.manage`).
     */
    public function delete(int $id)
    {
        if (! $this->isAdmin()) {
            return $this->forbiddenResponse();
        }

        if ($this->service->findById($id) === null) {
            return $this->notFoundResponse();
        }

        $result = $this->service->delete($id);

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    /**
     * POST /api/games/(:num)/cover
     * Restrito a administradores (permissão `games.manage`).
     */
    public function uploadCover(int $id)
    {
        if (! $this->isAdmin()) {
            return $this->forbiddenResponse();
        }

        if ($this->service->findById($id) === null) {
            return $this->notFoundResponse();
        }

        $file = $this->request->getFile('cover');

        $validation = service('validation')->setRules([
            'cover' => 'uploaded[cover]|max_size[cover,2048]|is_image[cover]|mime_in[cover,image/jpg,image/jpeg,image/png,image/webp]',
        ]);

        // File rules read the upload straight from the request; the $data
        // array must NOT contain the UploadedFile object itself.
        if ($file === null || ! $validation->run($this->request->getPost())) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => lang('Game.invalidCoverFile'),
                'errors'  => $validation->getErrors(),
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/games', $newName);

        $result = $this->service->updateCoverImage($id, 'uploads/games/' . $newName);

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    /**
     * Somente jogadores autenticados com a permissão `games.manage`
     * (grupos `admin` e `superadmin`) podem cadastrar, editar ou excluir jogos.
     */
    private function isAdmin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can('games.manage');
    }

    private function forbiddenResponse()
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
            'status'  => 'error',
            'message' => lang('Game.forbidden'),
        ]);
    }

    private function notFoundResponse()
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
            'status'  => 'error',
            'message' => lang('Game.notFound'),
        ]);
    }

    private function getInput(): array
    {
        $json = $this->request->getJSON(true);

        return is_array($json) ? $json : $this->request->getPost();
    }
}
