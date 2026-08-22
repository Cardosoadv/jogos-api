<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\User;

/**
 * @property string|null $name
 * @property string|null $avatar
 * @property Time|null   $birth_date
 * @property string|null $phone
 * @property string|null $bio
 */
class Player extends User
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => '?integer',
        'active'      => 'int-bool',
        'permissions' => 'array',
        'groups'      => 'array',
        'birth_date'  => '?datetime',
    ];
}
