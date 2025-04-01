<?php
namespace App\Models;

use CodeIgniter\Model;

class UnidadeEnderecoModel extends Model
{
    protected $table      = 'unidade_endereco';
    protected $allowedFields = [
        'unid_id',
        'end_id'
    ];
}