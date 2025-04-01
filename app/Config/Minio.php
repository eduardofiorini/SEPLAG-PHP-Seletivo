<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Minio extends BaseConfig
{
    /**
     * MinIO endpoint URL
     *
     * @var string
     */
    public $endpoint = '';

    /**
     * MinIO access key
     *
     * @var string
     */
    public $accessKey = '';

    /**
     * MinIO secret key
     *
     * @var string
     */
    public $secretKey = '';

    /**
     * Region for S3 compatibility
     *
     * @var string
     */
    public $region = 'us-east-1';

    /**
     * Default bucket name
     *
     * @var string
     */
    public $defaultBucket = 'uploads';

    /**
     * Whether to use path style endpoint
     *
     * @var bool
     */
    public $usePathStyleEndpoint = true;

    public function __construct()
    {
        parent::__construct();

        $this->endpoint = getenv('minio.endpoint') ?: $this->endpoint;
        $this->accessKey = getenv('minio.access.key') ?: $this->accessKey;
        $this->secretKey = getenv('minio.secret.key') ?: $this->secretKey;
        $this->region = getenv('minio.region') ?: $this->region;
        $this->defaultBucket = getenv('minio.default.bucket') ?: $this->defaultBucket;
        $this->usePathStyleEndpoint = (bool)(getenv('minio.use.path.style') ?: $this->usePathStyleEndpoint);
    }
}