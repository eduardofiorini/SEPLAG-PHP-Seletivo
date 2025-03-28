<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pessoa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'pes_nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'pes_data_nascimento' => [
                'type' => 'DATE'
            ],
            'pes_sexo' => [
                'type' => 'VARCHAR',
                'constraint' => 9
            ],
            'pes_mae' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
            'pes_pai' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ]
        ]);
        $this->forge->addPrimaryKey('pes_id');
        $this->forge->createTable('pessoa');
    }

    public function down()
    {
        $this->forge->dropTable('pessoa');
    }
}
