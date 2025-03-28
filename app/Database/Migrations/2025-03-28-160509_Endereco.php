<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Endereco extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'end_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'end_tipo_logradouro' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'end_logradouro' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'end_numero' => [
                'type' => 'INT',
                'constraint' => 11
            ],
            'end_bairro' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'cid_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ]
        ]);
        $this->forge->addPrimaryKey('end_id');
        $this->forge->createTable('endereco');
    }

    public function down()
    {
        $this->forge->dropTable('endereco');
    }
}
