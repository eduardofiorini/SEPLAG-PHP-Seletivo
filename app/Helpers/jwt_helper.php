<?php
use App\Models\AuthModel;
use Firebase\JWT\JWT;

if(!function_exists('jwtRequest')) {
    function jwtRequest($authHeader){
        if (is_null($authHeader)) {
            throw new Exception('Token de acesso ausente ou inválido.');
        }
        return explode(' ', $authHeader)[1];
    }
}

if(!function_exists('jwtValidateRequest')) {
    function jwtValidateRequest(string $token)
    {
        $key = getenv('jwt.privateKey');
        $decode = JWT::decode($token, $key, ['HS256']);
        $authModel = new AuthModel();
        return $authModel->where('email', $decode->email)->first();
    }
}

if(!function_exists('jwtSignature')) {
    function jwtSignature(string $email)
    {
        $key = getenv('jwt.privateKey');
        $time = time();
        $expiration = $time + getenv('jwt.lifeTime');
        $payload = [
            'email' => $email,
            'iat' => $time,
            'exp' => $expiration,
        ];
        return JWT::encode($payload, $key);
    }
}