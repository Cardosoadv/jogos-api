<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BaseRepository;
use Exception;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function findAll(int $limit = 0, int $offset = 0): array
    {
        return $this->repository->findAll($limit, $offset);
    }


    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    /**
     * Helper para respostas de sucesso
     */
    protected function success(string $message, array $data = []): array
    {
        return array_merge(['status' => 'success', 'message' => $message], $data);
    }

    /**
     * Helper para respostas de erro
     */
    protected function error(string $message, array $data = []): array
    {
        return array_merge(['status' => 'error', 'message' => $message], $data);
    }

    public function create(array $data): array
    {
        try {
            $db = \Config\Database::connect();
            $db->transStart();
            $id = $this->repository->create($data);
            $db->transComplete();

            if ($db->transStatus() === false || !$id) {
                return $this->error(lang('BaseService.createError'));
            }

            return $this->success(lang('BaseService.createSuccess'), ['id' => $id]);
        } catch (Exception $e) {
            return $this->error(lang('BaseService.internalError', [$e->getMessage()]));
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $db = \Config\Database::connect();
            $db->transStart();
            $result = $this->repository->update($id, $data);
            $db->transComplete();

            if ($db->transStatus() === false || !$result) {
                return $this->error(lang('BaseService.updateError'));
            }

            return $this->success(lang('BaseService.updateSuccess'));
        } catch (Exception $e) {
            return $this->error(lang('BaseService.internalError', [$e->getMessage()]));
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->repository->delete($id);

            if (!$result) {
                return $this->error(lang('BaseService.deleteError'));
            }

            return $this->success(lang('BaseService.deleteSuccess'));
        } catch (Exception $e) {
            return $this->error(lang('BaseService.internalError', [$e->getMessage()]));
        }
    }

    public function getPager(): object
    {
        return $this->repository->getPager();
    }

    public function getRepository(): BaseRepository
    {
        return $this->repository;
    }
}
