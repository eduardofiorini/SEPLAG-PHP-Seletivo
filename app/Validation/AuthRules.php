<?php

namespace App\Validation;

use App\Models\AuthModel;
use Exception;

class AuthRules
{
    public function validateAuthPassword(string $str, string $fields, array $data): bool
    {
        try {
            $model = new AuthModel();
            $obj = $model->where('auth_email', $data['email'])->first();

            if (!$obj) {
                return false;
            }

            return password_verify($data['senha'], $obj['auth_senha']);
        } catch (Exception $e) {
            return false;
        }
    }
}
