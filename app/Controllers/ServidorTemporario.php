<?php

namespace App\Controllers;

use App\Models\ServidorTemporarioModel;
use App\Models\PessoaModel;
use App\Models\FotoPessoaModel;
use App\Models\PessoaEnderecoModel;
use App\Models\EnderecoModel;
use App\Models\CidadeModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

/**
 * @OA\Tag(
 *     name="ServidoresTemporarios",
 *     description="API Endpoints para gerenciamento de servidores temporários"
 * )
 */
class ServidorTemporario extends ResourceController
{
    use ResponseTrait;

    protected ServidorTemporarioModel $servidorTemporarioModel;
    protected PessoaModel $pessoaModel;
    protected FotoPessoaModel $fotoPessoaModel;
    protected PessoaEnderecoModel $pessoaEnderecoModel;
    protected EnderecoModel $enderecoModel;
    protected CidadeModel $cidadeModel;

    public function __construct()
    {
        $this->servidorTemporarioModel = new ServidorTemporarioModel();
        $this->pessoaModel = new PessoaModel();
        $this->fotoPessoaModel = new FotoPessoaModel();
        $this->pessoaEnderecoModel = new PessoaEnderecoModel();
        $this->enderecoModel = new EnderecoModel();
        $this->cidadeModel = new CidadeModel();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/servidores-temporarios",
     *     tags={"ServidoresTemporarios"},
     *     summary="Listar todos os servidores temporários",
     *     description="Retorna uma lista paginada dos servidores temporários no formato JSON simplificado",
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
     *         description="Lista de servidores temporários",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="currentPage", type="integer"),
     *                 @OA\Property(property="totalPages", type="integer"),
     *                 @OA\Property(property="perPage", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
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

        $servidores = $this->servidorTemporarioModel->findAll($limit, $offset);
        $servidoresTransformados = [];

        foreach ($servidores as $servidor) {
            $pesId = $servidor['pes_id'];
            $servidorTransformado = $this->transformServidorFormat($pesId);
            if ($servidorTransformado) {
                $servidoresTransformados[] = $servidorTransformado;
            }
        }

        $total = $this->servidorTemporarioModel->countAllResults();
        $totalPages = ceil($total / $limit);

        $response = [
            'data' => $servidoresTransformados,
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
     *     path="/api/v1/servidores-temporarios/{id}",
     *     tags={"ServidoresTemporarios"},
     *     summary="Buscar servidor temporário por ID",
     *     description="Retorna os dados de um servidor temporário específico no formato JSON simplificado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do servidor temporário (mesmo que pes_id)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do servidor temporário",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servidor temporário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function show($id = null)
    {
        $servidor = $this->servidorTemporarioModel->find($id);

        if (!$servidor) {
            return $this->failNotFound('Servidor temporário não encontrado');
        }

        $servidorTransformado = $this->transformServidorFormat($id);

        if (!$servidorTransformado) {
            return $this->failNotFound('Erro ao buscar detalhes do servidor temporário');
        }

        return $this->respond($servidorTransformado);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/servidores-temporarios",
     *     tags={"ServidoresTemporarios"},
     *     summary="Cadastrar novo servidor temporário",
     *     description="Cria um novo registro de servidor temporário",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do servidor temporário",
     *         @OA\JsonContent(
     *             required={"pes_nome", "pes_data_nascimento", "pes_sexo", "pes_mae", "servidor_temporario", "endereco"},
     *             @OA\Property(property="pes_nome", type="string"),
     *             @OA\Property(property="pes_data_nascimento", type="string", format="date"),
     *             @OA\Property(property="pes_sexo", type="string", enum={"M", "F"}),
     *             @OA\Property(property="pes_mae", type="string"),
     *             @OA\Property(property="pes_pai", type="string"),
     *             @OA\Property(
     *                 property="servidor_temporario",
     *                 type="object",
     *                 @OA\Property(property="st_data_admissao", type="string", format="date"),
     *                 @OA\Property(property="st_data_demissao", type="string", format="date")
     *             ),
     *             @OA\Property(
     *                 property="endereco",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="end_tipo_logradouro", type="string"),
     *                     @OA\Property(property="end_logradouro", type="string"),
     *                     @OA\Property(property="end_numero", type="string"),
     *                     @OA\Property(property="end_bairro", type="string"),
     *                     @OA\Property(
     *                         property="cidade",
     *                         type="object",
     *                         @OA\Property(property="cid_id", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Servidor temporário criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação nos dados fornecidos"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cidade não encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function create()
    {
        $rules = [
            'pes_nome' => 'required|min_length[3]|max_length[200]',
            'pes_data_nascimento' => 'required|valid_date',
            'pes_sexo' => 'required|in_list[M,F]',
            'pes_mae' => 'required|min_length[3]|max_length[200]',
            'pes_pai' => 'permit_empty|min_length[3]|max_length[200]',
            'servidor_temporario.st_data_admissao' => 'required|valid_date',
            'servidor_temporario.st_data_demissao' => 'permit_empty|valid_date',
            'endereco.0.end_tipo_logradouro' => 'required|max_length[50]',
            'endereco.0.end_logradouro' => 'required|max_length[200]',
            'endereco.0.end_numero' => 'required',
            'endereco.0.end_bairro' => 'required|max_length[100]',
            'endereco.0.cidade.cid_id' => 'required|integer|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $requestData = $this->request->getJSON(true);

        // Extrai os dados de pessoa
        $pessoaData = [
            'pes_nome' => $requestData['pes_nome'],
            'pes_data_nascimento' => $requestData['pes_data_nascimento'],
            'pes_sexo' => $requestData['pes_sexo'],
            'pes_mae' => $requestData['pes_mae'],
            'pes_pai' => $requestData['pes_pai'] ?? null
        ];

        // Extrai os dados do servidor temporário
        $servidorData = $requestData['servidor_temporario'] ?? null;

        // Extrai os dados do endereço
        $enderecoData = null;
        if (isset($requestData['endereco']) && is_array($requestData['endereco']) && !empty($requestData['endereco'])) {
            $enderecoData = [
                'end_tipo_logradouro' => $requestData['endereco'][0]['end_tipo_logradouro'],
                'end_logradouro' => $requestData['endereco'][0]['end_logradouro'],
                'end_numero' => $requestData['endereco'][0]['end_numero'],
                'end_bairro' => $requestData['endereco'][0]['end_bairro'],
                'cid_id' => $requestData['endereco'][0]['cidade']['cid_id']
            ];
        }

        // Verificar se a cidade existe
        if ($enderecoData && isset($enderecoData['cid_id'])) {
            $cidade = $this->cidadeModel->find($enderecoData['cid_id']);
            if (!$cidade) {
                return $this->failNotFound('Cidade não encontrada');
            }
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Inserir pessoa
            $pesId = $this->pessoaModel->insert($pessoaData);
            if (!$pesId) {
                throw new \Exception('Erro ao cadastrar pessoa');
            }

            // 2. Inserir servidor temporário
            $servidorData['pes_id'] = $pesId;
            $this->servidorTemporarioModel->insert($servidorData);

            // 3. Inserir endereço se existir
            if ($enderecoData) {
                $endId = $this->enderecoModel->insert($enderecoData);
                if (!$endId) {
                    throw new \Exception('Erro ao cadastrar endereço');
                }

                // 4. Relacionar pessoa com endereço
                $this->pessoaEnderecoModel->insert([
                    'pes_id' => $pesId,
                    'end_id' => $endId
                ]);
            }

            $db->transCommit();

            // Buscar o registro completo para retornar
            $servidorTransformado = $this->transformServidorFormat($pesId);
            return $this->respondCreated($servidorTransformado);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/servidores-temporarios/{id}",
     *     tags={"ServidoresTemporarios"},
     *     summary="Atualizar servidor temporário",
     *     description="Atualiza os dados de um servidor temporário específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do servidor temporário (mesmo que pes_id)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Dados para atualização do servidor temporário",
     *         @OA\JsonContent(
     *             @OA\Property(property="pes_nome", type="string"),
     *             @OA\Property(property="pes_data_nascimento", type="string", format="date"),
     *             @OA\Property(property="pes_sexo", type="string", enum={"M", "F"}),
     *             @OA\Property(property="pes_mae", type="string"),
     *             @OA\Property(property="pes_pai", type="string"),
     *             @OA\Property(
     *                 property="servidor_temporario",
     *                 type="object",
     *                 @OA\Property(property="st_data_admissao", type="string", format="date"),
     *                 @OA\Property(property="st_data_demissao", type="string", format="date")
     *             ),
     *             @OA\Property(
     *                 property="endereco",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="end_tipo_logradouro", type="string"),
     *                     @OA\Property(property="end_logradouro", type="string"),
     *                     @OA\Property(property="end_numero", type="string"),
     *                     @OA\Property(property="end_bairro", type="string"),
     *                     @OA\Property(
     *                         property="cidade",
     *                         type="object",
     *                         @OA\Property(property="cid_id", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servidor temporário atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação nos dados fornecidos"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servidor temporário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function update($id = null)
    {
        $servidor = $this->servidorTemporarioModel->find($id);
        if (!$servidor) {
            return $this->failNotFound('Servidor temporário não encontrado');
        }

        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->failNotFound('Pessoa não encontrada');
        }

        $rules = [
            'pes_nome' => 'permit_empty|min_length[3]|max_length[200]',
            'pes_data_nascimento' => 'permit_empty|valid_date',
            'pes_sexo' => 'permit_empty|in_list[M,F]',
            'pes_mae' => 'permit_empty|min_length[3]|max_length[200]',
            'pes_pai' => 'permit_empty|min_length[3]|max_length[200]',
            'servidor_temporario.st_data_admissao' => 'permit_empty|valid_date',
            'servidor_temporario.st_data_demissao' => 'permit_empty|valid_date',
            'endereco.0.end_tipo_logradouro' => 'permit_empty|max_length[50]',
            'endereco.0.end_logradouro' => 'permit_empty|max_length[200]',
            'endereco.0.end_numero' => 'permit_empty',
            'endereco.0.end_bairro' => 'permit_empty|max_length[100]',
            'endereco.0.cidade.cid_id' => 'permit_empty|integer|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $requestData = $this->request->getJSON(true);

        // Extrai os dados de pessoa
        $pessoaData = [];
        if (isset($requestData['pes_nome'])) $pessoaData['pes_nome'] = $requestData['pes_nome'];
        if (isset($requestData['pes_data_nascimento'])) $pessoaData['pes_data_nascimento'] = $requestData['pes_data_nascimento'];
        if (isset($requestData['pes_sexo'])) $pessoaData['pes_sexo'] = $requestData['pes_sexo'];
        if (isset($requestData['pes_mae'])) $pessoaData['pes_mae'] = $requestData['pes_mae'];
        if (isset($requestData['pes_pai'])) $pessoaData['pes_pai'] = $requestData['pes_pai'];

        // Extrai os dados do servidor temporário
        $servidorData = $requestData['servidor_temporario'] ?? null;

        // Extrai os dados do endereço
        $enderecoData = null;
        if (isset($requestData['endereco']) && is_array($requestData['endereco']) && !empty($requestData['endereco'])) {
            $enderecoData = [];
            if (isset($requestData['endereco'][0]['end_tipo_logradouro'])) $enderecoData['end_tipo_logradouro'] = $requestData['endereco'][0]['end_tipo_logradouro'];
            if (isset($requestData['endereco'][0]['end_logradouro'])) $enderecoData['end_logradouro'] = $requestData['endereco'][0]['end_logradouro'];
            if (isset($requestData['endereco'][0]['end_numero'])) $enderecoData['end_numero'] = $requestData['endereco'][0]['end_numero'];
            if (isset($requestData['endereco'][0]['end_bairro'])) $enderecoData['end_bairro'] = $requestData['endereco'][0]['end_bairro'];
            if (isset($requestData['endereco'][0]['cidade']['cid_id'])) $enderecoData['cid_id'] = $requestData['endereco'][0]['cidade']['cid_id'];
        }

        // Verificar se a cidade existe (se fornecida)
        if ($enderecoData && isset($enderecoData['cid_id'])) {
            $cidade = $this->cidadeModel->find($enderecoData['cid_id']);
            if (!$cidade) {
                return $this->failNotFound('Cidade não encontrada');
            }
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Atualizar dados da pessoa
            if (!empty($pessoaData)) {
                $this->pessoaModel->update($id, $pessoaData);
            }

            // 2. Atualizar dados do servidor temporário
            if ($servidorData) {
                $this->servidorTemporarioModel->update($id, $servidorData);
            }

            // 3. Atualizar ou criar endereço
            if ($enderecoData && !empty($enderecoData)) {
                $pessoaEndereco = $this->pessoaEnderecoModel->where('pes_id', $id)->first();

                if ($pessoaEndereco) {
                    // Atualizar endereço existente
                    $this->enderecoModel->update($pessoaEndereco['end_id'], $enderecoData);
                } else {
                    // Criar novo endereço e relacionamento
                    $endId = $this->enderecoModel->insert($enderecoData);
                    $this->pessoaEnderecoModel->insert([
                        'pes_id' => $id,
                        'end_id' => $endId
                    ]);
                }
            }

            $db->transCommit();

            // Buscar o registro completo para retornar
            $servidorTransformado = $this->transformServidorFormat($id);
            return $this->respond($servidorTransformado);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/servidores-temporarios/{id}",
     *     tags={"ServidoresTemporarios"},
     *     summary="Excluir servidor temporário",
     *     description="Remove o cadastro de um servidor temporário específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do servidor temporário (mesmo que pes_id)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="deleteRelated",
     *         in="query",
     *         required=false,
     *         description="Flag para excluir registros relacionados (pessoa, endereço, etc.)",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servidor temporário excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servidor temporário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function delete($id = null)
    {
        $servidor = $this->servidorTemporarioModel->find($id);
        if (!$servidor) {
            return $this->failNotFound('Servidor temporário não encontrado');
        }

        $deleteRelated = filter_var($this->request->getVar('deleteRelated'), FILTER_VALIDATE_BOOLEAN);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Excluir o registro de servidor temporário
            $this->servidorTemporarioModel->delete($id);

            if ($deleteRelated) {
                // Buscar e excluir foto da pessoa
                $fotoPessoa = $this->fotoPessoaModel->where('pes_id', $id)->first();
                if ($fotoPessoa) {
                    $this->fotoPessoaModel->delete($fotoPessoa['fp_id']);
                }

                // Buscar e excluir relacionamento pessoa_endereco
                $pessoaEndereco = $this->pessoaEnderecoModel->where('pes_id', $id)->first();
                if ($pessoaEndereco) {
                    $endId = $pessoaEndereco['end_id'];
                    $this->pessoaEnderecoModel->where('pes_id', $id)->delete();

                    // Verificar se o endereço não está sendo usado por outra pessoa antes de excluir
                    $enderecoEmUso = $this->pessoaEnderecoModel->where('end_id', $endId)->first();
                    if (!$enderecoEmUso) {
                        $this->enderecoModel->delete($endId);
                    }
                }

                // Excluir a pessoa
                $this->pessoaModel->delete($id);
            }

            $db->transCommit();

            return $this->respond([
                'message' => 'Servidor temporário excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/servidores-temporarios/{id}/foto",
     *     tags={"ServidoresTemporarios"},
     *     summary="Upload de foto do servidor temporário",
     *     description="Realiza o upload da foto de um servidor temporário específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do servidor temporário (mesmo que pes_id)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="foto",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo de imagem para upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Foto atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="fp_id", type="integer"),
     *                 @OA\Property(property="fp_data", type="string", format="date"),
     *                 @OA\Property(property="fp_bucket", type="string"),
     *                 @OA\Property(property="fp_hash", type="string"),
     *                 @OA\Property(property="pes_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Foto cadastrada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação nos dados fornecidos"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servidor temporário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function uploadFoto($id = null)
    {
        // Verificar se o servidor temporário existe
        $servidor = $this->servidorTemporarioModel->find($id);
        if (!$servidor) {
            return $this->failNotFound('Servidor temporário não encontrado');
        }

        // Validação do arquivo
        $validationRules = [
            'foto' => [
                'label' => 'Imagem',
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|max_size[foto,2048]',
            ],
        ];

        if (!$this->validate($validationRules)) {
            return $this->fail($this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Obter o arquivo uploaded
            $file = $this->request->getFile('foto');

            if (!$file->isValid()) {
                throw new \Exception('Arquivo inválido');
            }

            // Gerar nome único para o arquivo
            $newName = $file->getRandomName();

            // Configuração do cliente MinIO
            $minioConfig = config('Minio');
            $bucket = $minioConfig->defaultBucket;

            // Inicializar cliente MinIO
            $minioClient = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $minioConfig->region,
                'endpoint' => $minioConfig->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $minioConfig->accessKey,
                    'secret' => $minioConfig->secretKey,
                ],
            ]);

            // Verificar se o bucket existe, se não, criar
            if (!$minioClient->doesBucketExist($bucket)) {
                $minioClient->createBucket(['Bucket' => $bucket]);
            }

            // Upload do arquivo para o MinIO
            $fileContent = file_get_contents($file->getTempName());
            $hash = md5($fileContent);

            $result = $minioClient->putObject([
                'Bucket' => $bucket,
                'Key'    => $newName,
                'Body'   => $fileContent,
                'ContentType' => $file->getMimeType()
            ]);

            // Preparar dados para salvar no banco
            $fotoData = [
                'fp_data' => date('Y-m-d'),
                'fp_bucket' => $bucket,
                'fp_hash' => $hash,
                'pes_id' => $id
            ];

            // Verificar se já existe uma foto para esta pessoa
            $fotoExistente = $this->fotoPessoaModel->where('pes_id', $id)->first();

            if ($fotoExistente) {
                // Se existir uma foto anterior, excluir do MinIO
                try {
                    $minioClient->deleteObject([
                        'Bucket' => $fotoExistente['fp_bucket'],
                        'Key'    => $fotoExistente['fp_hash']
                    ]);
                } catch (\Exception $e) {
                    // Log do erro, mas continua o processo
                    log_message('error', 'Erro ao excluir foto anterior: ' . $e->getMessage());
                }

                // Atualizar registro existente
                $this->fotoPessoaModel->update($fotoExistente['fp_id'], $fotoData);
                $message = 'Foto atualizada com sucesso';
                $statusCode = 200;
                $fpId = $fotoExistente['fp_id'];
            } else {
                // Inserir novo registro
                $fpId = $this->fotoPessoaModel->insert($fotoData);
                $message = 'Foto cadastrada com sucesso';
                $statusCode = 201;
            }

            $db->transCommit();

            // Preparar resposta completa
            $responseData = [
                'foto_pessoa' => [
                    'fp_id' => $fpId,
                    'fp_data' => $fotoData['fp_data'],
                    'fp_bucket' => $fotoData['fp_bucket'],
                    'fp_hash' => $fotoData['fp_hash']
                ]
            ];

            return $this->respond([
                'message' => $message,
                'data' => $responseData
            ], $statusCode);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/servidores-temporarios/{id}/foto",
     *     tags={"ServidoresTemporarios"},
     *     summary="Excluir foto do servidor temporário",
     *     description="Remove a foto de um servidor temporário específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do servidor temporário (mesmo que pes_id)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Foto excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Foto excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servidor temporário não encontrado ou foto não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Servidor temporário não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function deleteFoto($id = null)
    {
        // Verificar se o servidor temporário existe
        $servidor = $this->servidorTemporarioModel->find($id);
        if (!$servidor) {
            return $this->failNotFound('Servidor temporário não encontrado');
        }

        // Verificar se existe uma foto para este servidor
        $foto = $this->fotoPessoaModel->where('pes_id', $id)->first();
        if (!$foto) {
            return $this->failNotFound('Foto não encontrada para este servidor');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Configuração do cliente MinIO
            $minioConfig = config('Minio');
            $bucket = $foto['fp_bucket']; // Usar o bucket salvo no registro

            // Inicializar cliente MinIO
            $minioClient = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $minioConfig->region,
                'endpoint' => $minioConfig->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $minioConfig->accessKey,
                    'secret' => $minioConfig->secretKey,
                ],
            ]);

            // Verificar se o objeto existe no bucket
            if ($minioClient->doesObjectExist($bucket, $foto['fp_hash'])) {
                // Excluir o arquivo do MinIO
                $minioClient->deleteObject([
                    'Bucket' => $bucket,
                    'Key'    => $foto['fp_hash']
                ]);
            } else {
                log_message('warning', 'Arquivo não encontrado no bucket: ' . $bucket . ', hash: ' . $foto['fp_hash']);
            }

            // Excluir o registro do banco de dados
            $deleted = $this->fotoPessoaModel->delete($foto['fp_id']);

            if (!$deleted) {
                throw new \Exception('Erro ao excluir registro da foto');
            }

            $db->transCommit();

            return $this->respond([
                'message' => 'Foto excluída com sucesso'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro ao excluir foto: ' . $e->getMessage());
            return $this->fail('Erro ao excluir foto: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/servidores-temporarios/buscar-por-nome",
     *     tags={"ServidoresTemporarios"},
     *     summary="Buscar endereço funcional por nome do servidor temporário",
     *     description="Retorna o endereço funcional (da unidade onde é lotado) de servidores temporários a partir de uma parte do nome",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="nome",
     *         in="query",
     *         required=true,
     *         description="Parte do nome do servidor a ser pesquisado",
     *         @OA\Schema(type="string", minLength=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de servidores temporários e seus endereços funcionais",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="nome", type="string"),
     *                     @OA\Property(property="data_admissao", type="string", format="date"),
     *                     @OA\Property(property="data_demissao", type="string", format="date", nullable=true),
     *                     @OA\Property(property="unidade", type="string"),
     *                     @OA\Property(
     *                         property="endereco_funcional",
     *                         type="object",
     *                         @OA\Property(property="logradouro", type="string"),
     *                         @OA\Property(property="numero", type="string"),
     *                         @OA\Property(property="bairro", type="string"),
     *                         @OA\Property(property="cidade", type="string"),
     *                         @OA\Property(property="uf", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro nos parâmetros da requisição"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhum servidor temporário encontrado"
     *     )
     * )
     */
    public function buscarPorNome()
    {
        // Validar parâmetro de busca
        $nome = $this->request->getVar('nome');

        if (empty($nome) || strlen($nome) < 3) {
            return $this->fail('É necessário informar pelo menos 3 caracteres para a busca');
        }

        // Consulta para buscar servidores temporários pelo nome e seus respectivos endereços funcionais
        $db = \Config\Database::connect();

        $query = $db->table('pessoa p')
            ->select('
            p.pes_id as id,
            p.pes_nome as nome,
            st.st_data_admissao as data_admissao,
            st.st_data_demissao as data_demissao,
            u.unid_nome as unidade,
            u.unid_id as unidade_id,
            ue.end_id as endereco_id
        ')
            ->join('servidor_temporario st', 'p.pes_id = st.pes_id')
            ->join('lotacao l', 'p.pes_id = l.pes_id')
            ->join('unidade u', 'l.unid_id = u.unid_id')
            ->leftJoin('unidade_endereco ue', 'u.unid_id = ue.unid_id')
            ->where('p.pes_nome LIKE', "%$nome%")
            ->where('l.lot_data_remocao IS NULL') // Somente lotações ativas
            ->orderBy('p.pes_nome', 'ASC');

        $resultado = $query->get()->getResultArray();

        if (empty($resultado)) {
            return $this->failNotFound('Nenhum servidor temporário encontrado com o nome informado');
        }

        // Buscar detalhes dos endereços das unidades
        $servidores = [];
        foreach ($resultado as $row) {
            // Buscar endereço da unidade
            $enderecoFuncional = [];
            if (!empty($row['endereco_id'])) {
                $endereco = $db->table('endereco e')
                    ->select('
                    e.end_tipo_logradouro,
                    e.end_logradouro,
                    e.end_numero,
                    e.end_bairro,
                    c.cid_nome as cidade,
                    c.cid_uf as uf
                ')
                    ->join('cidade c', 'e.cid_id = c.cid_id')
                    ->where('e.end_id', $row['endereco_id'])
                    ->get()
                    ->getRowArray();

                if ($endereco) {
                    $enderecoFuncional = [
                        'logradouro' => $endereco['end_tipo_logradouro'] . ' ' . $endereco['end_logradouro'],
                        'numero' => $endereco['end_numero'],
                        'bairro' => $endereco['end_bairro'],
                        'cidade' => $endereco['cidade'],
                        'uf' => $endereco['uf']
                    ];
                }
            }

            // Adicionar à lista de resultados
            $servidores[] = [
                'id' => (int)$row['id'],
                'nome' => $row['nome'],
                'data_admissao' => $row['data_admissao'],
                'data_demissao' => $row['data_demissao'],
                'unidade' => $row['unidade'],
                'endereco_funcional' => $enderecoFuncional ?: null
            ];
        }

        return $this->respond([
            'data' => $servidores
        ]);
    }

    /**
     * Transforma os dados de um servidor temporário em um formato padronizado para a API
     *
     * @param int $pesId ID da pessoa/servidor
     * @return array|null Array com os dados formatados ou null se não encontrado
     */
    private function transformServidorFormat($pesId)
    {
        // Buscar dados da pessoa
        $pessoa = $this->pessoaModel->find($pesId);
        if (!$pessoa) {
            return null;
        }

        // Buscar dados do servidor temporário
        $servidor = $this->servidorTemporarioModel->find($pesId);
        if (!$servidor) {
            return null;
        }

        // Buscar endereço da pessoa
        $pessoaEndereco = $this->pessoaEnderecoModel->where('pes_id', $pesId)->first();
        $endereco = null;
        $cidade = null;

        if ($pessoaEndereco) {
            $endereco = $this->enderecoModel->find($pessoaEndereco['end_id']);
            if ($endereco && isset($endereco['cid_id'])) {
                $cidade = $this->cidadeModel->find($endereco['cid_id']);
            }
        }

        // Buscar foto da pessoa
        $foto = $this->fotoPessoaModel->where('pes_id', $pesId)->first();

        // Montar resposta formatada
        $result = [
            'id' => (int)$pesId,
            'pes_nome' => $pessoa['pes_nome'],
            'pes_data_nascimento' => $pessoa['pes_data_nascimento'],
            'pes_sexo' => $pessoa['pes_sexo'],
            'pes_mae' => $pessoa['pes_mae'],
            'pes_pai' => $pessoa['pes_pai'] ?? null,
            'servidor_temporario' => [
                'st_data_admissao' => $servidor['st_data_admissao'],
                'st_data_demissao' => $servidor['st_data_demissao'] ?? null
            ]
        ];

        // Adicionar endereço se existir
        if ($endereco) {
            $enderecoFormatado = [
                'end_tipo_logradouro' => $endereco['end_tipo_logradouro'],
                'end_logradouro' => $endereco['end_logradouro'],
                'end_numero' => $endereco['end_numero'],
                'end_bairro' => $endereco['end_bairro']
            ];

            // Adicionar cidade se existir
            if ($cidade) {
                $enderecoFormatado['cidade'] = [
                    'cid_id' => (int)$cidade['cid_id'],
                    'cid_nome' => $cidade['cid_nome'] ?? null,
                    'cid_uf' => $cidade['cid_uf'] ?? null
                ];
            }

            $result['endereco'] = [$enderecoFormatado];
        }

        // Adicionar informações da foto se existir
        if ($foto) {
            $temporaryLinkData = $this->getFotoLinkTemporario($foto['fp_id']);
            $result['foto_pessoa'] = [
                'fp_id' => (int)$foto['fp_id'],
                'fp_data' => $foto['fp_data'],
                'fp_bucket' => $foto['fp_bucket'],
                'fp_hash' => $foto['fp_hash'],
                'fp_url_temporary' =>  $temporaryLinkData['url'],
                'fp_url_expiration' => $temporaryLinkData['expiration'],
            ];
        }

        return $result;
    }

    /**
     * Gera um link temporário com validade de 5 minutos para acessar a foto pelo Min.IO
     *
     * @param int $fp_id ID da foto/servidor
     * @return array|null Array com URL e data de expiração ou null em caso de erro
     */
    private function getFotoLinkTemporario($fp_id = null)
    {
        // Verificar se o fp_id é válido
        if (!is_numeric($fp_id)) {
            return $this->fail('ID da foto inválido');
        }

        // Buscar a foto pelo fp_id
        $foto = $this->fotoPessoaModel->find($fp_id);
        if (!$foto) {
            return $this->failNotFound('Foto não encontrada');
        }

        try {
            // Configuração do cliente MinIO
            $minioConfig = config('Minio');
            $bucket = $foto['fp_bucket'];
            $objectKey = $foto['fp_hash'];

            // Inicializar cliente MinIO com AWS SDK
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $minioConfig->region,
                'endpoint' => $minioConfig->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $minioConfig->accessKey,
                    'secret' => $minioConfig->secretKey,
                ],
            ]);

            // Verificar se o objeto existe
            if (!$s3Client->doesObjectExist($bucket, $objectKey)) {
                return $this->failNotFound('Arquivo de imagem não encontrado no servidor');
            }

            // Gerar URL pré-assinada com expiração de 5 minutos (300 segundos)
            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $objectKey
            ]);

            $request = $s3Client->createPresignedRequest($command, '+5 minutes');
            $presignedUrl = (string) $request->getUri();

            // Calcular o horário de expiração (5 minutos a partir de agora)
            $expirationTime = (new \DateTime())->add(new \DateInterval('PT5M'))->format('c');

            return [
                'url' => $presignedUrl,
                'expiration' => $expirationTime
            ];

        } catch (\Exception $e) {
            log_message('error', 'Erro ao gerar link temporário: ' . $e->getMessage());
            return $this->fail('Erro ao gerar link temporário: ' . $e->getMessage());
        }
    }
}