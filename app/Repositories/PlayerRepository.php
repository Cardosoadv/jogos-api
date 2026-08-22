<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Player;
use App\Models\PlayerModel;

class PlayerRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PlayerModel();
    }

    /**
     * Overridden because the model's returnType is the Player entity,
     * not an array as the parent's signature expects.
     */
    public function findById(int $id): ?Player
    {
        return $this->model->withIdentities()->find($id);
    }

    /**
     * Overridden to eager-load identities, avoiding one email lookup
     * query per player when the list is serialized.
     */
    public function findAll(int $limit = 0, int $offset = 0): array
    {
        return $this->model->withIdentities()->findAll($limit, $offset);
    }

    public function findByEmail(string $email): ?Player
    {
        /** @var PlayerModel $model */
        $model = $this->model;

        return $model->findByCredentials(['email' => $email]);
    }

    public function findByUsername(string $username): ?Player
    {
        return $this->model->where('username', $username)->first();
    }
}
