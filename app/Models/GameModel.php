<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Game;
use CodeIgniter\Model;

class GameModel extends Model
{
    protected $table         = 'games';
    protected $primaryKey    = 'id';
    protected $returnType    = Game::class;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'title',
        'description',
        'category',
        'cover_image',
        'active',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'title'       => 'required|max_length[150]|min_length[2]',
        'description' => 'permit_empty|max_length[2000]',
        'category'    => 'permit_empty|max_length[50]',
        'cover_image' => 'permit_empty|max_length[255]',
        'active'      => 'permit_empty|in_list[0,1]',
    ];
}
