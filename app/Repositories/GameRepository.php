<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\GameModel;

class GameRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new GameModel();
    }
}
