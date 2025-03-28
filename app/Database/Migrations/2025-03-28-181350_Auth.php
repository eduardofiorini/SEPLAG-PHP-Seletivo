<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Auth extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'auth_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'auth_nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'auth_email' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'auth_senha' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ]
        ]);
        $this->forge->addPrimaryKey('auth_id');
        $this->forge->createTable('auth');
    }

    public function down()
    {
        $this->forge->dropTable('auth');
    }
}
