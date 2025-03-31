<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;

/**
 * @OA\Info(
 *     title="SEPLAG API Rest",
 *     version="1.0.0",
 *     description="API Rest criada para o processo seletivo da SEPLAG 2025."
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="Servidor de API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Insira o token JWT no formato: Bearer {token}"
 * )
 *
 *@OA\Tag(
 *     name="Autenticação",
 *     description="API Endpoints para autenticação de usuários"
 * )
 *
 *@OA\Schema(
 *     schema="Autenticação",
 *     title="Auth Model",
 *     description="Auth model data",
 *     @OA\Property(property="auth_id", type="integer", example=1),
 *     @OA\Property(property="auth_nome", type="string", example="Maria Aparecida da Silva"),
 *     @OA\Property(property="auth_email", type="string", example="teste@teste.com.br"),
 *     @OA\Property(property="auth_senha", type="string", example="senha@123")
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
     *     path="/api/v1/auth",
     *     tags={"Autenticação"},
     *     summary="Realiza a autenticação na api",
     *     description="Favor inserir os dados de email e senha (password)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "senha"},
     *             @OA\Property(property="email", type="string", format="email", example="teste@teste.com.br"),
     *             @OA\Property(property="senha", type="string", format="password", example="senha@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login com sucesso",
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
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="E-mail não encontrado",
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
     *     path="/api/v1/auth/registro",
     *     tags={"Autenticação"},
     *     summary="Cadastrar de novos usuários",
     *     description="Criar novo usuário para acesso",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha"},
     *             @OA\Property(property="nome", type="string", example="Maria Aparecida da Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="teste@teste.com.br"),
     *             @OA\Property(property="senha", type="string", format="password", example="senha@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="auth_id", type="integer"),
     *             @OA\Property(property="auth_nome", type="string"),
     *             @OA\Property(property="auth_email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function register()
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
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     tags={"Autenticação"},
     *     summary="Refresh token autenticação",
     *     description="Gerar um novo token usando o token existente válido",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="exp", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado - Token inválido ou expirado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $key = getenv('jwt.privateKey');
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return $this->failUnauthorized('Token não fornecido');
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $iat = time();
            $exp = $iat + getenv('jwt.lifeTime');

            $payload = [
                'iss' => 'API SEPLAG SELETIVO',
                'sub' => $decoded->sub,
                'iat' => $iat,
                'exp' => $exp,
                'email' => $decoded->email,
                'nome' => $decoded->nome
            ];

            $newToken = JWT::encode($payload, $key, 'HS256');

            return $this->respond([
                'token' => $newToken,
                'exp' => $exp
            ]);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token inválido ou expirado');
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Desconectar Usuário",
     *     description="Sair do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Desconectado com sucesso",
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