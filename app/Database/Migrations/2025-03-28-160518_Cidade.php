<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Cidade extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'cid_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'cid_nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'cid_uf' => [
                'type' => 'CHAR',
                'constraint' => 2
            ]
        ]);
        $this->forge->addPrimaryKey('cid_id');
        $this->forge->createTable('cidade');
    }

    public function down()
    {
        $this->forge->dropTable('cidade');
    }
}
