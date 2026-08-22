<?php

declare(strict_types=1);

namespace App\Repositories;

use CodeIgniter\Model;

/**
 * Base class for repositories
 *
 * @property Model $model
 * @method findAll(int $limit = 0, int $offset = 0): array
 * @method findById(int $id): ?array
 * @method create(array $data): int
 * @method update(int $id, array $data): bool
 * @method delete(int $id): bool
 */
abstract class BaseRepository
{
    protected Model $model;

    /**
     * Busca todos os registros
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(int $limit = 0, int $offset = 0): array
    {
        return $this->model->findAll($limit, $offset);
    }

    /**
     * Return type is widened to `array|object|null` (instead of `?array`)
     * because models whose `$returnType` is an Entity (e.g. PlayerModel)
     * return an object here, not a plain array.
     *
     * @param int $id
     * @return array|object|null
     */
    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        return (int) $this->model->insert($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * @param int $perPage
     * @return array
     */
    public function paginate(int $perPage = 20): array
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @return object
     */
    public function getPager()
    {
        return $this->model->pager;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}
