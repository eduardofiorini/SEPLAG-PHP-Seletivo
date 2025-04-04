<?php
namespace App\Models;

use CodeIgniter\Model;

class ServidorTemporarioModel extends Model
{
    protected $table      = 'servidor_temporario';
    protected $primaryKey = 'pes_id';
    protected $allowedFields = [
        'pes_id',
        'st_data_admissao',
        'st_data_demissao'
    ];
}