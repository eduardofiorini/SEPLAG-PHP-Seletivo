<?php
namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table      = 'auth';
    protected $primaryKey = 'auth_id';
    protected $allowedFields = [
        'auth_nome', 
        'auth_email', 
        'auth_senha'
    ];
}