<?php
namespace App\Models;

use CodeIgniter\Model;

class ServidorEfetivoModel extends Model
{
    protected $table      = 'servidor_efetivo';
    protected $primaryKey = 'pes_id';
    protected $allowedFields = [
        'pes_id',
        'se_matricula'
    ];
}