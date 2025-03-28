<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Lotacao extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'lot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'pes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'unid_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'lot_data_lotacao' => [
                'type' => 'DATE'
            ],
            'lot_data_remocao' => [
                'type' => 'DATE',
                'null' => true
            ],
            'lot_portaria' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ]
        ]);
        $this->forge->addPrimaryKey('lot_id');
        $this->forge->createTable('lotacao');
    }

    public function down()
    {
        $this->forge->dropTable('lotacao');
    }
}
