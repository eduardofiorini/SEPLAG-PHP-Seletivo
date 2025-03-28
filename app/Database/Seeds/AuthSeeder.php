<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'auth_nome'  => 'Administrador',
                'auth_email' => 'admin@admin.com.br',
                'auth_senha' => password_hash('Ezm&F7#G5&c2', PASSWORD_DEFAULT),
            ]
        ];

        $this->db->table('auth')->insertBatch($data);
    }
}
