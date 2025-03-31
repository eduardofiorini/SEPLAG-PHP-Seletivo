<?php

namespace App\Controllers;

use App\Models\UnidadeModel;
use App\Models\UnidadeEnderecoModel;
use App\Models\EnderecoModel;
use App\Models\CidadeModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

/**
 * @OA\Tag(
 *     name="Unidades",
 *     description="API Endpoints para gerenciamento de unidades"
 * )
 *
 * @OA\Schema(
 *     schema="Cidade",
 *     title="Cidade Model",
 *     description="Cidade model data",
 *     @OA\Property(property="cid_id", type="integer", example=1),
 *     @OA\Property(property="cid_nome", type="string", example="Rio de Janeiro"),
 *     @OA\Property(property="cid_uf", type="string", example="RJ")
 * )
 *
 * @OA\Schema(
 *     schema="Endereco",
 *     title="Endereco Model",
 *     description="Endereco model data",
 *     @OA\Property(property="end_id", type="integer", example=1),
 *     @OA\Property(property="end_tipo_logradouro", type="string", example="Rua"),
 *     @OA\Property(property="end_logradouro", type="string", example="Exemplo"),
 *     @OA\Property(property="end_numero", type="integer", example=123),
 *     @OA\Property(property="end_bairro", type="string", example="Centro"),
 *     @OA\Property(property="cidade", type="object", ref="#/components/schemas/Cidade")
 * )
 *
 * @OA\Schema(
 *     schema="Unidade",
 *     title="Unidade Model",
 *     description="Unidade model data",
 *     @OA\Property(property="unid_id", type="integer", example=1),
 *     @OA\Property(property="unid_nome", type="string", example="Secretaria de Saúde"),
 *     @OA\Property(property="unid_sigla", type="string", example="SESAU"),
 *     @OA\Property(property="endereco", type="array", @OA\Items(ref="#/components/schemas/Endereco"))
 * )
 */
class Unidade extends ResourceController
{
    use ResponseTrait;

    protected $unidadeModel;
    protected $unidadeEnderecoModel;
    protected $enderecoModel;
    protected $cidadeModel;

    public function __construct()
    {
        $this->unidadeModel = new UnidadeModel();
        $this->unidadeEnderecoModel = new UnidadeEnderecoModel();
        $this->enderecoModel = new EnderecoModel();
        $this->cidadeModel = new CidadeModel();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/unidades",
     *     tags={"Unidades"},
     *     summary="Listar todas as unidades",
     *     description="Retorna uma lista paginada das unidades cadastradas com seus endereços",
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
     *         description="Lista de unidades",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Unidade")),
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

        $unidades = $this->unidadeModel->findAll($limit, $offset);

        // Carregar os endereços para cada unidade
        foreach ($unidades as &$unidade) {
            $unidade['endereco'] = $this->getEnderecosUnidade($unidade['unid_id']);
        }

        $total = $this->unidadeModel->countAllResults();
        $totalPages = ceil($total / $limit);

        $response = [
            'data' => $unidades,
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
     *     path="/api/v1/unidades/{id}",
     *     tags={"Unidades"},
     *     summary="Buscar unidade por ID",
     *     description="Retorna os dados de uma unidade específica com seus endereços",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da unidade",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados da unidade",
     *         @OA\JsonContent(ref="#/components/schemas/Unidade")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unidade não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unidade não encontrada")
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
        $unidade = $this->unidadeModel->find($id);

        if (!$unidade) {
            return $this->failNotFound('Unidade não encontrada');
        }

        // Carregar endereços da unidade
        $unidade['endereco'] = $this->getEnderecosUnidade($id);

        return $this->respond($unidade);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/unidades",
     *     tags={"Unidades"},
     *     summary="Cadastrar nova unidade",
     *     description="Cria um novo registro de unidade com seus endereços",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unid_nome", "unid_sigla"},
     *             @OA\Property(property="unid_nome", type="string", example="Secretaria de Saúde"),
     *             @OA\Property(property="unid_sigla", type="string", example="SESAU"),
     *             @OA\Property(property="endereco", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="end_tipo_logradouro", type="string", example="Avenida"),
     *                     @OA\Property(property="end_logradouro", type="string", example="Rio Branco"),
     *                     @OA\Property(property="end_numero", type="integer", example="123"),
     *                     @OA\Property(property="end_bairro", type="string", example="Centro"),
     *                     @OA\Property(property="cidade", type="object",
     *                         @OA\Property(property="cid_id", type="integer", example="1"),
     *                         @OA\Property(property="cid_nome", type="string", example="Rio de Janeiro"),
     *                         @OA\Property(property="cid_uf", type="string", example="RJ")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Unidade cadastrada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Unidade")
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
            'unid_nome' => 'required|min_length[3]|max_length[100]',
            'unid_sigla' => 'required|min_length[1]|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'unid_nome' => $this->request->getVar('unid_nome'),
            'unid_sigla' => $this->request->getVar('unid_sigla')
        ];

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Inserir a unidade
            $unidadeId = $this->unidadeModel->insert($data);

            if (!$unidadeId) {
                throw new \Exception('Erro ao cadastrar unidade');
            }

            // Processar endereços se existirem
            $enderecos = $this->request->getVar('endereco');
            if (is_array($enderecos) && count($enderecos) > 0) {
                foreach ($enderecos as $enderecoData) {
                    $cidadeData = $enderecoData['cidade'] ?? null;

                    // Verificar se a cidade já existe ou precisa ser criada
                    $cidadeId = null;
                    if (!empty($cidadeData['cid_id'])) {
                        $cidade = $this->cidadeModel->find($cidadeData['cid_id']);
                        if ($cidade) {
                            $cidadeId = $cidadeData['cid_id'];
                        }
                    }

                    if (!$cidadeId && !empty($cidadeData['cid_nome']) && !empty($cidadeData['cid_uf'])) {
                        // Buscar cidade por nome e UF
                        $cidade = $this->cidadeModel->where('cid_nome', $cidadeData['cid_nome'])
                            ->where('cid_uf', $cidadeData['cid_uf'])
                            ->first();

                        if ($cidade) {
                            $cidadeId = $cidade['cid_id'];
                        } else {
                            // Criar nova cidade
                            $novaCidade = [
                                'cid_nome' => $cidadeData['cid_nome'],
                                'cid_uf' => $cidadeData['cid_uf']
                            ];
                            $cidadeId = $this->cidadeModel->insert($novaCidade);
                        }
                    }

                    if ($cidadeId) {
                        // Criar novo endereço
                        $novoEndereco = [
                            'end_tipo_logradouro' => $enderecoData['end_tipo_logradouro'] ?? '',
                            'end_logradouro' => $enderecoData['end_logradouro'] ?? '',
                            'end_numero' => $enderecoData['end_numero'] ?? null,
                            'end_bairro' => $enderecoData['end_bairro'] ?? '',
                            'cid_id' => $cidadeId
                        ];

                        $enderecoId = $this->enderecoModel->insert($novoEndereco);

                        if ($enderecoId) {
                            // Criar relação entre unidade e endereço
                            $this->unidadeEnderecoModel->insert([
                                'unid_id' => $unidadeId,
                                'end_id' => $enderecoId
                            ]);
                        }
                    }
                }
            }

            $db->transCommit();

            // Retornar a unidade completa com endereços
            $unidade = $this->unidadeModel->find($unidadeId);
            $unidade['endereco'] = $this->getEnderecosUnidade($unidadeId);

            return $this->respondCreated($unidade);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/unidades/{id}",
     *     tags={"Unidades"},
     *     summary="Atualizar unidade",
     *     description="Atualiza os dados de uma unidade específica e seus endereços",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da unidade",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="unid_nome", type="string", example="Secretaria de Saúde Atualizada"),
     *             @OA\Property(property="unid_sigla", type="string", example="SESAU"),
     *             @OA\Property(property="endereco", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="end_id", type="integer", example="1"),
     *                     @OA\Property(property="end_tipo_logradouro", type="string", example="Avenida"),
     *                     @OA\Property(property="end_logradouro", type="string", example="Rio Branco"),
     *                     @OA\Property(property="end_numero", type="integer", example="123"),
     *                     @OA\Property(property="end_bairro", type="string", example="Centro"),
     *                     @OA\Property(property="cidade", type="object",
     *                         @OA\Property(property="cid_id", type="integer", example="1"),
     *                         @OA\Property(property="cid_nome", type="string", example="Rio de Janeiro"),
     *                         @OA\Property(property="cid_uf", type="string", example="RJ")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unidade atualizada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Unidade")
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
     *         description="Unidade não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unidade não encontrada")
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
        $unidade = $this->unidadeModel->find($id);

        if (!$unidade) {
            return $this->failNotFound('Unidade não encontrada');
        }

        $rules = [
            'unid_nome' => 'required|min_length[3]|max_length[100]',
            'unid_sigla' => 'required|min_length[1]|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'unid_nome' => $this->request->getVar('unid_nome'),
            'unid_sigla' => $this->request->getVar('unid_sigla')
        ];

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Atualizar dados da unidade
            $updated = $this->unidadeModel->update($id, $data);

            if (!$updated) {
                throw new \Exception('Erro ao atualizar unidade');
            }

            // Atualizar endereços
            $enderecos = $this->request->getVar('endereco');
            if (is_array($enderecos)) {
                foreach ($enderecos as $enderecoData) {
                    $enderecoId = $enderecoData['end_id'] ?? null;
                    $cidadeData = $enderecoData['cidade'] ?? null;

                    // Processar cidade
                    $cidadeId = null;
                    if (!empty($cidadeData['cid_id'])) {
                        $cidade = $this->cidadeModel->find($cidadeData['cid_id']);
                        if ($cidade) {
                            $cidadeId = $cidadeData['cid_id'];

                            // Atualizar dados da cidade se necessário
                            if ((!empty($cidadeData['cid_nome']) && $cidadeData['cid_nome'] != $cidade['cid_nome']) ||
                                (!empty($cidadeData['cid_uf']) && $cidadeData['cid_uf'] != $cidade['cid_uf'])) {

                                $cidadeUpdateData = [];
                                if (!empty($cidadeData['cid_nome'])) {
                                    $cidadeUpdateData['cid_nome'] = $cidadeData['cid_nome'];
                                }
                                if (!empty($cidadeData['cid_uf'])) {
                                    $cidadeUpdateData['cid_uf'] = $cidadeData['cid_uf'];
                                }

                                if (!empty($cidadeUpdateData)) {
                                    $this->cidadeModel->update($cidadeId, $cidadeUpdateData);
                                }
                            }
                        }
                    }

                    if (!$cidadeId && !empty($cidadeData['cid_nome']) && !empty($cidadeData['cid_uf'])) {
                        // Buscar cidade por nome e UF
                        $cidade = $this->cidadeModel->where('cid_nome', $cidadeData['cid_nome'])
                            ->where('cid_uf', $cidadeData['cid_uf'])
                            ->first();

                        if ($cidade) {
                            $cidadeId = $cidade['cid_id'];
                        } else {
                            // Criar nova cidade
                            $novaCidade = [
                                'cid_nome' => $cidadeData['cid_nome'],
                                'cid_uf' => $cidadeData['cid_uf']
                            ];
                            $cidadeId = $this->cidadeModel->insert($novaCidade);
                        }
                    }

                    // Processar endereço
                    if ($enderecoId) {
                        // Atualizar endereço existente
                        $endereco = $this->enderecoModel->find($enderecoId);

                        if ($endereco) {
                            $enderecoUpdateData = [];

                            if (!empty($enderecoData['end_tipo_logradouro'])) {
                                $enderecoUpdateData['end_tipo_logradouro'] = $enderecoData['end_tipo_logradouro'];
                            }
                            if (!empty($enderecoData['end_logradouro'])) {
                                $enderecoUpdateData['end_logradouro'] = $enderecoData['end_logradouro'];
                            }
                            if (isset($enderecoData['end_numero'])) {
                                $enderecoUpdateData['end_numero'] = $enderecoData['end_numero'];
                            }
                            if (!empty($enderecoData['end_bairro'])) {
                                $enderecoUpdateData['end_bairro'] = $enderecoData['end_bairro'];
                            }
                            if ($cidadeId) {
                                $enderecoUpdateData['cid_id'] = $cidadeId;
                            }

                            if (!empty($enderecoUpdateData)) {
                                $this->enderecoModel->update($enderecoId, $enderecoUpdateData);
                            }

                            // Verificar se o endereço já está associado à unidade
                            $relacao = $this->unidadeEnderecoModel->where('unid_id', $id)
                                ->where('end_id', $enderecoId)
                                ->first();

                            if (!$relacao) {
                                // Criar relação se não existir
                                $this->unidadeEnderecoModel->insert([
                                    'unid_id' => $id,
                                    'end_id' => $enderecoId
                                ]);
                            }
                        }
                    } else if ($cidadeId) {
                        // Criar novo endereço
                        $novoEndereco = [
                            'end_tipo_logradouro' => $enderecoData['end_tipo_logradouro'] ?? '',
                            'end_logradouro' => $enderecoData['end_logradouro'] ?? '',
                            'end_numero' => $enderecoData['end_numero'] ?? null,
                            'end_bairro' => $enderecoData['end_bairro'] ?? '',
                            'cid_id' => $cidadeId
                        ];

                        $novoEnderecoId = $this->enderecoModel->insert($novoEndereco);

                        if ($novoEnderecoId) {
                            // Criar relação entre unidade e endereço
                            $this->unidadeEnderecoModel->insert([
                                'unid_id' => $id,
                                'end_id' => $novoEnderecoId
                            ]);
                        }
                    }
                }
            }

            $db->transCommit();

            // Retornar unidade atualizada com endereços
            $unidade = $this->unidadeModel->find($id);
            $unidade['endereco'] = $this->getEnderecosUnidade($id);

            return $this->respond($unidade);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/unidades/{id}",
     *     tags={"Unidades"},
     *     summary="Excluir unidade",
     *     description="Remove o cadastro de uma unidade específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da unidade",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unidade excluída com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unidade excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unidade não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unidade não encontrada")
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
        $unidade = $this->unidadeModel->find($id);

        if (!$unidade) {
            return $this->failNotFound('Unidade não encontrada');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Remover relações de endereços
            $this->unidadeEnderecoModel->where('unid_id', $id)->delete();

            // Excluir a unidade
            $deleted = $this->unidadeModel->delete($id);

            if (!$deleted) {
                throw new \Exception('Erro ao excluir unidade');
            }

            $db->transCommit();

            return $this->respondDeleted(['message' => 'Unidade excluída com sucesso']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Método auxiliar para obter os endereços de uma unidade
     */
    private function getEnderecosUnidade($unidadeId)
    {
        $enderecos = [];

        // Buscar relações unidade-endereço
        $relacoes = $this->unidadeEnderecoModel->where('unid_id', $unidadeId)->findAll();

        foreach ($relacoes as $relacao) {
            $endereco = $this->enderecoModel->find($relacao['end_id']);

            if ($endereco) {
                // Buscar dados da cidade
                $cidade = null;
                if (!empty($endereco['cid_id'])) {
                    $cidade = $this->cidadeModel->find($endereco['cid_id']);
                }

                // Incluir cidade no objeto de endereço
                $endereco['cidade'] = $cidade ?? [
                    'cid_id' => '',
                    'cid_nome' => '',
                    'cid_uf' => ''
                ];

                $enderecos[] = $endereco;
            }
        }

        return $enderecos;
    }
}