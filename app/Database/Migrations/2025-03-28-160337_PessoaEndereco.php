<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PessoaEndereco extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'end_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ]
        ]);
        $this->forge->addPrimaryKey(['pes_id', 'end_id']);
        $this->forge->createTable('pessoa_endereco');
    }

    public function down()
    {
        $this->forge->dropTable('pessoa_endereco');
    }
}
