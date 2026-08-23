<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null    $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $category
 * @property string|null $cover_image
 * @property bool|null   $active
 */
class Game extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'     => '?integer',
        'active' => 'int-bool',
    ];
}
