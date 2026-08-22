<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Player;
use CodeIgniter\Shield\Models\UserModel;

class PlayerModel extends UserModel
{
    protected $returnType    = Player::class;
    protected $allowedFields = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'name',
        'avatar',
        'birth_date',
        'phone',
        'bio',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'username'   => 'permit_empty|max_length[30]|min_length[3]|alpha_numeric_punct|is_unique[users.username,id,{id}]',
        'name'       => 'permit_empty|max_length[150]',
        'avatar'     => 'permit_empty|max_length[255]',
        'birth_date' => 'permit_empty|valid_date',
        'phone'      => 'permit_empty|max_length[20]',
        'bio'        => 'permit_empty|max_length[500]',
    ];
}
