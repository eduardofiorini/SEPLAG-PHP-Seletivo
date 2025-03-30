<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;

/**
 * @OA\Info(
 *     title="SEPLAG API Seletivo",
 *     version="1.0.0",
 *     description="API de autenticação para o sistema SEPLAG Seletivo"
 * )
 */
class Auth extends ResourceController
{
    use ResponseTrait;
    private AuthModel $authModel;
    public function __construct()
    {
        helper('cookie');
        $this->authModel = new AuthModel();
    }

    /**
     * @OA\Post(
     *     path="/auth",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Login with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "senha"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="senha", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="exp", type="integer"),
     *             @OA\Property(
     *                 property="usuario",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="nome", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Email não encontrado")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $rules = [
            'email' => 'required|valid_email',
            'senha' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $user = $this->authModel->where('auth_email', $this->request->getVar('email'))->first();

        if (!$user) {
            return $this->failNotFound('Email não encontrado');
        }

        $verify = password_verify($this->request->getVar('senha'), $user['auth_senha']);

        if (!$verify) {
            return $this->fail('Senha incorreta');
        }

        $key = getenv('jwt.privateKey');
        $iat = time();
        $exp = $iat + getenv('jwt.lifeTime');

        $payload = [
            'iss' => 'API SEPLAG SELETIVO',
            'sub' => $user['auth_id'],
            'iat' => $iat,
            'exp' => $exp,
            'email' => $user['auth_email'],
            'nome' => $user['auth_nome']
        ];


        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond([
            'token' => $token,
            'exp' => $exp,
            'usuario' => [
                'id' => $user['auth_id'],
                'email' => $user['auth_email'],
                'nome' => $user['auth_nome']
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/registro",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     description="Create a new user account",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha"},
     *             @OA\Property(property="nome", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="senha", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="auth_id", type="integer"),
     *             @OA\Property(property="auth_nome", type="string"),
     *             @OA\Property(property="auth_email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function registro()
    {
        $rules = [
            'nome' => 'required',
            'email' => 'required|valid_email|is_unique[auth.auth_email]',
            'senha' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'auth_nome' => $this->request->getVar('nome'),
            'auth_email' => $this->request->getVar('email'),
            'auth_senha' => password_hash($this->request->getVar('senha'), PASSWORD_DEFAULT)
        ];

        $data['auth_id'] = $this->authModel->insert($data);
        unset($data['auth_senha']);

        return $this->respondCreated($data);
    }

    /**
     * @OA\Get(
     *     path="/auth/perfil",
     *     tags={"User"},
     *     summary="Get user profile",
     *     description="Retrieve the authenticated user's profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or expired token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function perfil()
    {
        $key = getenv('jwt.privateKey');
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $response = [
                'id' => $decoded->sub,
                'email' => $decoded->email,
                'name' => $decoded->name
            ];
            return $this->respond($response);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token inválido ou expirado');
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     description="Logout the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        return $this->respond(['message' => 'Logout realizado com sucesso']);
    }
}