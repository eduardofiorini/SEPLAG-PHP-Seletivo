<?php

namespace App\Controllers;

use App\Models\LotacaoModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

/**
 * @OA\Tag(
 *     name="Lotações",
 *     description="API Endpoints para gerenciamento de lotações"
 * )
 *
 * @OA\Schema(
 *     schema="Lotacao",
 *     title="Lotacao Model",
 *     description="Lotacao model data",
 *     @OA\Property(property="lot_id", type="integer", example=1),
 *     @OA\Property(property="pes_id", type="integer", example=23),
 *     @OA\Property(property="unid_id", type="integer", example=5),
 *     @OA\Property(property="lot_data_lotacao", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="lot_data_remocao", type="string", format="date", example="2025-03-01"),
 *     @OA\Property(property="lot_portaria", type="string", example="Portaria Nº 123/2025")
 * )
 */
class Lotacao extends ResourceController
{
    use ResponseTrait;

    protected $lotacaoModel;

    public function __construct()
    {
        $this->lotacaoModel = new LotacaoModel();
    }

    /**
     * @OA\Get(
     *     path="/lotacoes",
     *     tags={"Lotações"},
     *     summary="Listar todas as lotações",
     *     description="Retorna uma lista paginada das lotações cadastradas",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Número da página (padrão: 1)",
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Quantidade de registros por página (padrão: 10, máximo: 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de lotações",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lotacao")),
     *             @OA\Property(property="pagination", type="object",
     *                @OA\Property(property="currentPage", type="integer", example=1),
     *                @OA\Property(property="totalPages", type="integer", example=10),
     *                @OA\Property(property="perPage", type="integer", example=10),
     *                @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $limit = $this->request->getVar('limit') ?? 10;

        $page = max(1, (int)$page);
        $limit = min(100, max(1, (int)$limit));
        $offset = ($page - 1) * $limit;

        $lotacoes = $this->lotacaoModel->findAll($limit, $offset);

        $total = $this->lotacaoModel->countAllResults();
        $totalPages = ceil($total / $limit);

        $response = [
            'data' => $lotacoes,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $limit,
                'total' => $total
            ]
        ];
        return $this->respond($response);
    }

    /**
     * @OA\Get(
     *     path="/lotacoes/{id}",
     *     tags={"Lotações"},
     *     summary="Buscar lotação por ID",
     *     description="Retorna os dados de uma lotação específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da lotação",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados da lotação",
     *         @OA\JsonContent(ref="#/components/schemas/Lotacao")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lotação não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Lotação não encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function show($id = null)
    {
        $lotacao = $this->lotacaoModel->find($id);

        if (!$lotacao) {
            return $this->failNotFound('Lotação não encontrada');
        }

        return $this->respond($lotacao);
    }

    /**
     * @OA\Post(
     *     path="/lotacoes",
     *     tags={"Lotações"},
     *     summary="Cadastrar nova lotação",
     *     description="Cria um novo registro de lotação",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pes_id", "unid_id", "lot_data_lotacao"},
     *             @OA\Property(property="pes_id", type="integer", example=23),
     *             @OA\Property(property="unid_id", type="integer", example=5),
     *             @OA\Property(property="lot_data_lotacao", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="lot_data_remocao", type="string", format="date", example="2025-03-01"),
     *             @OA\Property(property="lot_portaria", type="string", example="Portaria Nº 123/2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lotação cadastrada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Lotacao")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function create()
    {
        $rules = [
            'pes_id' => 'required|integer',
            'unid_id' => 'required|integer',
            'lot_data_lotacao' => 'required|valid_date',
            'lot_data_remocao' => 'permit_empty|valid_date',
            'lot_portaria' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'pes_id' => $this->request->getVar('pes_id'),
            'unid_id' => $this->request->getVar('unid_id'),
            'lot_data_lotacao' => $this->request->getVar('lot_data_lotacao'),
            'lot_data_remocao' => $this->request->getVar('lot_data_remocao'),
            'lot_portaria' => $this->request->getVar('lot_portaria')
        ];

        $id = $this->lotacaoModel->insert($data);

        if ($id) {
            $data['lot_id'] = $id;
            return $this->respondCreated($data);
        } else {
            return $this->fail('Erro ao cadastrar lotação');
        }
    }

    /**
     * @OA\Put(
     *     path="/lotacoes/{id}",
     *     tags={"Lotações"},
     *     summary="Atualizar lotação",
     *     description="Atualiza os dados de uma lotação específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da lotação",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="pes_id", type="integer", example=23),
     *             @OA\Property(property="unid_id", type="integer", example=5),
     *             @OA\Property(property="lot_data_lotacao", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="lot_data_remocao", type="string", format="date", example="2025-03-01"),
     *             @OA\Property(property="lot_portaria", type="string", example="Portaria Nº 123/2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lotação atualizada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Lotacao")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lotação não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Lotação não encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function update($id = null)
    {
        $lotacao = $this->lotacaoModel->find($id);

        if (!$lotacao) {
            return $this->failNotFound('Lotação não encontrada');
        }

        $rules = [
            'pes_id' => 'required|integer',
            'unid_id' => 'required|integer',
            'lot_data_lotacao' => 'required|valid_date',
            'lot_data_remocao' => 'permit_empty|valid_date',
            'lot_portaria' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'pes_id' => $this->request->getVar('pes_id'),
            'unid_id' => $this->request->getVar('unid_id'),
            'lot_data_lotacao' => $this->request->getVar('lot_data_lotacao'),
            'lot_data_remocao' => $this->request->getVar('lot_data_remocao'),
            'lot_portaria' => $this->request->getVar('lot_portaria')
        ];

        $updated = $this->lotacaoModel->update($id, $data);

        if ($updated) {
            $data['lot_id'] = $id;
            return $this->respond($data);
        } else {
            return $this->fail('Erro ao atualizar lotação');
        }
    }

    /**
     * @OA\Delete(
     *     path="/lotacoes/{id}",
     *     tags={"Lotações"},
     *     summary="Excluir lotação",
     *     description="Remove o cadastro de uma lotação específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da lotação",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lotação excluída com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Lotação excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lotação não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Lotação não encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     )
     * )
     */
    public function delete($id = null)
    {
        $lotacao = $this->lotacaoModel->find($id);

        if (!$lotacao) {
            return $this->failNotFound('Lotação não encontrada');
        }

        $deleted = $this->lotacaoModel->delete($id);

        if ($deleted) {
            return $this->respondDeleted(['message' => 'Lotação excluída com sucesso']);
        } else {
            return $this->fail('Erro ao excluir lotação');
        }
    }
}