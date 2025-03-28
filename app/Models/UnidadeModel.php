<?php
namespace App\Models;

use CodeIgniter\Model;

class UnidadeModel extends Model
{
    protected $table      = 'unidade';
    protected $primaryKey = 'unid_id';
    protected $allowedFields = [
        'unid_nome', 
        'unid_sigla'
    ];
}