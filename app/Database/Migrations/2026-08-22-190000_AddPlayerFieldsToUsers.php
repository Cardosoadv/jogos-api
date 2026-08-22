<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlayerFieldsToUsers extends Migration
{
    private string $table = 'users';

    public function up(): void
    {
        $this->forge->addColumn($this->table, [
            'name' => [
                'type'       => 'varchar',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'username',
            ],
            'avatar' => [
                'type'       => 'varchar',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
            ],
            'birth_date' => [
                'type'  => 'date',
                'null'  => true,
                'after' => 'avatar',
            ],
            'phone' => [
                'type'       => 'varchar',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'birth_date',
            ],
            'bio' => [
                'type'       => 'varchar',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'phone',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn($this->table, ['name', 'avatar', 'birth_date', 'phone', 'bio']);
    }
}
