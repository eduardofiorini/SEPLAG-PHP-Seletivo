<?php
namespace App\Models;

use CodeIgniter\Model;

class LotacaoModel extends Model
{
    protected $table      = 'lotacao';
    protected $primaryKey = 'lot_id';
    protected $allowedFields = [
        'pes_id', 
        'unid_id', 
        'lot_data_lotacao', 
        'lot_data_remocao', 
        'lot_portaria'
    ];
}