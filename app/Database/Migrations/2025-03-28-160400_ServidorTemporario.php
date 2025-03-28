<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ServidorTemporario extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'st_data_admissao' => [
                'type' => 'DATE'
            ],
            'st_data_demissao' => [
                'type' => 'DATE',
                'null' => true
            ]
        ]);
        $this->forge->addPrimaryKey('pes_id');
        $this->forge->createTable('servidor_temporario');
    }

    public function down()
    {
        $this->forge->dropTable('servidor_temporario');
    }
}
