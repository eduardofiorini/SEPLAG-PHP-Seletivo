<?php
namespace App\Models;

use CodeIgniter\Model;

class UnidadeEnderecoModel extends Model
{
    protected $table      = 'unidade_endereco';
    protected $primaryKey = 'unid_id';
    protected $allowedFields = [
        'end_id'
    ];
}