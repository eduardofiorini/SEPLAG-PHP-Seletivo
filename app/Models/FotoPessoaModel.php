<?php
namespace App\Models;

use CodeIgniter\Model;

class FotoPessoaModel extends Model
{
    protected $table      = 'foto_pessoa';
    protected $primaryKey = 'fp_id';
    protected $allowedFields = [
        'pes_id', 
        'fp_data', 
        'fp_bucket', 
        'fp_hash'
    ];
}