<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\PlayerService;
use CodeIgniter\HTTP\ResponseInterface;

class Player extends BaseController
{

    public function __construct(?PlayerService $service = null) {
        $this->service = $service ?? new PlayerService();
    }

    /**
     * GET /api/players
     */
    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $offset  = ($page - 1) * $perPage;

        $players = $this->service->findAll($perPage, $offset);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $players,
        ]);
    }

    /**
     * GET /api/players/(:num)
     */
    public function show(int $id)
    {
        $player = $this->service->findById($id);

        if ($player === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => lang('Player.notFound'),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $player,
        ]);
    }

    /**
     * POST /api/players
     * Registro público de um novo jogador (fluxo de registro do Shield).
     */
    public function create()
    {
        $result = $this->service->register($this->getInput());

        if ($result['status'] === 'error') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON($result);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON($result);
    }

    /**
     * PUT/PATCH /api/players/(:num)
     */
    public function update(int $id)
    {
        if (! $this->canManage($id)) {
            return $this->forbiddenResponse();
        }

        $data = $this->getInput();
        unset($data['email'], $data['password'], $data['username'], $data['active']);

        if ($this->service->findById($id) === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => lang('Player.notFound'),
            ]);
        }

        $result = $this->service->update($id, $data);

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    /**
     * DELETE /api/players/(:num)
     */
    public function delete(int $id)
    {
        if (! $this->canManage($id, 'users.delete')) {
            return $this->forbiddenResponse();
        }

        if ($this->service->findById($id) === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => lang('Player.notFound'),
            ]);
        }

        $result = $this->service->delete($id);

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    /**
     * POST /api/players/(:num)/avatar
     */
    public function uploadAvatar(int $id)
    {
        if (! $this->canManage($id)) {
            return $this->forbiddenResponse();
        }

        if ($this->service->findById($id) === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => lang('Player.notFound'),
            ]);
        }

        $file = $this->request->getFile('avatar');

        $validation = service('validation')->setRules([
            'avatar' => 'uploaded[avatar]|max_size[avatar,2048]|is_image[avatar]|mime_in[avatar,image/jpg,image/jpeg,image/png,image/webp]',
        ]);

        // File rules read the upload straight from the request; the $data
        // array must NOT contain the UploadedFile object itself.
        if ($file === null || ! $validation->run($this->request->getPost())) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => lang('Player.invalidAvatarFile'),
                'errors'  => $validation->getErrors(),
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/avatars', $newName);

        $result = $this->service->updateAvatar($id, 'uploads/avatars/' . $newName);

        return $this->response->setStatusCode(
            $result['status'] === 'success' ? ResponseInterface::HTTP_OK : ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
        )->setJSON($result);
    }

    private function canManage(int $id, string $permission = 'users.edit'): bool
    {
        $user = auth()->user();

        return $user !== null && ((int) $user->id === $id || $user->can($permission));
    }

    private function forbiddenResponse()
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
            'status'  => 'error',
            'message' => lang('Player.forbidden'),
        ]);
    }

    private function getInput(): array
    {
        $json = $this->request->getJSON(true);

        return is_array($json) ? $json : $this->request->getPost();
    }
}
