<?php
namespace App\Models;

use CodeIgniter\Model;

class PessoaEnderecoModel extends Model
{
    protected $table      = 'pessoa_endereco';
    protected $primaryKey = 'pes_id';
    protected $allowedFields = [
        'end_id'
    ];
}