<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Unidade extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'unid_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'unid_nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'unid_sigla' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ]
        ]);
        $this->forge->addPrimaryKey('unid_id');
        $this->forge->createTable('unidade');
    }

    public function down()
    {
        $this->forge->dropTable('unidade');
    }
}
