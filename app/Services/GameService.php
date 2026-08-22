<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Game;
use App\Repositories\GameRepository;
use Config\Database;
use Exception;

class GameService extends BaseService
{
    public function __construct()
    {
        $this->repository = new GameRepository();
    }

    public function findAll(int $limit = 0, int $offset = 0): array
    {
        /** @var Game[] $games */
        $games = $this->repository->findAll($limit, $offset);

        return array_map(fn (Game $game) => $this->toArray($game), $games);
    }

    public function findById(int $id): ?array
    {
        /** @var Game|null $game */
        $game = $this->repository->findById($id);

        return $game === null ? null : $this->toArray($game);
    }

    public function create(array $data): array
    {
        $rules = [
            'title'       => 'required|max_length[150]|min_length[2]',
            'description' => 'permit_empty|max_length[2000]',
            'category'    => 'permit_empty|max_length[50]',
        ];

        $validation = service('validation')->setRules($rules);

        if (! $validation->run($data)) {
            return $this->error(lang('Game.createError'), ['errors' => $validation->getErrors()]);
        }

        try {
            $db = Database::connect();
            $db->transStart();
            $id = $this->repository->create(array_intersect_key($data, $rules));
            $db->transComplete();

            if ($db->transStatus() === false || ! $id) {
                return $this->error(lang('Game.createError'));
            }

            /** @var Game $game */
            $game = $this->repository->findById($id);

            return $this->success(lang('Game.createSuccess'), ['game' => $this->toArray($game)]);
        } catch (Exception $e) {
            return $this->error(lang('BaseService.internalError', [$e->getMessage()]));
        }
    }

    public function update(int $id, array $data): array
    {
        $rules = [
            'title'       => 'permit_empty|max_length[150]|min_length[2]',
            'description' => 'permit_empty|max_length[2000]',
            'category'    => 'permit_empty|max_length[50]',
            'active'      => 'permit_empty|in_list[0,1]',
        ];

        $validation = service('validation')->setRules($rules);

        if (! $validation->run($data)) {
            return $this->error(lang('Game.updateError'), ['errors' => $validation->getErrors()]);
        }

        return parent::update($id, array_intersect_key($data, $rules));
    }

    public function updateCoverImage(int $id, string $path): array
    {
        return parent::update($id, ['cover_image' => $path]);
    }

    private function toArray(Game $game): array
    {
        return [
            'id'          => $game->id,
            'title'       => $game->title,
            'description' => $game->description,
            'category'    => $game->category,
            'cover_image' => $game->cover_image,
            'active'      => (bool) $game->active,
            'created_at'  => $game->created_at !== null ? (string) $game->created_at : null,
            'updated_at'  => $game->updated_at !== null ? (string) $game->updated_at : null,
        ];
    }
}
