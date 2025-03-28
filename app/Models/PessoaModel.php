<?php
namespace App\Models;

use CodeIgniter\Model;

class PessoaModel extends Model
{
    protected $table      = 'pessoa';
    protected $primaryKey = 'pes_id';
    protected $allowedFields = [
        'pes_nome', 
        'pes_data_nascimento', 
        'pes_sexo', 
        'pes_mae', 
        'pes_pai'
    ];
}