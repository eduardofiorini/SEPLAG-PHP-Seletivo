<?php
namespace App\Models;

use CodeIgniter\Model;

class EnderecoModel extends Model
{
    protected $table      = 'endereco';
    protected $primaryKey = 'end_id';
    protected $allowedFields = [
        'end_tipo_logradouro', 
        'end_logradouro', 
        'end_numero', 
        'end_bairro', 
        'cid_id'
    ];
}