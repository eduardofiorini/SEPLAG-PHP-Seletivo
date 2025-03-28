<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FotoPessoa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'fp_id' => [
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
            'fp_data' => [
                'type' => 'DATE'
            ],
            'fp_bucket' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'fp_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ]
        ]);
        $this->forge->addPrimaryKey('fp_id');
        $this->forge->createTable('foto_pessoa');
    }

    public function down()
    {
        $this->forge->dropTable('foto_pessoa');
    }
}
