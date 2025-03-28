<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ServidorEfetivo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'se_matricula' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ]
        ]);
        $this->forge->addPrimaryKey('pes_id');
        $this->forge->createTable('servidor_efetivo');
    }

    public function down()
    {
        $this->forge->dropTable('servidor_efetivo');
    }
}
