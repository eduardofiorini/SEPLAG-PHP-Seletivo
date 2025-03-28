<?php
namespace App\Models;

use CodeIgniter\Model;

class CidadeModel extends Model
{
    protected $table      = 'cidade';
    protected $primaryKey = 'cid_id';
    protected $allowedFields = [
        'cid_nome', 
        'cid_uf'
    ];
}