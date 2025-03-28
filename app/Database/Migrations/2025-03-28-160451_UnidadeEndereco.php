<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UnidadeEndereco extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'unid_id' => [
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
        $this->forge->addPrimaryKey(['unid_id', 'end_id']);
        $this->forge->createTable('unidade_endereco');
    }

    public function down()
    {
        $this->forge->dropTable('unidade_endereco');
    }
}
