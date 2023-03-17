<?php


namespace mhunesi\storage\filesystem;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Cached\CachedAdapter;
use yii\base\InvalidConfigException;

/**
 * AwsS3Filesystem
 */
class AwsS3Filesystem extends Filesystem
{
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $secret;
    /**
     * @var string
     */
    public $region;
    /**
     * @var string
     */
    public $baseUrl;
    /**
     * @var string
     */
    public $version;
    /**
     * @var string
     */
    public $bucket;
    /**
     * @var string|null
     */
    public $prefix;
    /**
     * @var bool
     */
    public $pathStyleEndpoint = false;
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var bool
     */
    public $streamReads = false;
    /**
     * @var string
     */
    public $endpoint;
    /**
     * @var array|\Aws\CacheInterface|\Aws\Credentials\CredentialsInterface|bool|callable
     */
    public $credentials;

    public $publicUrl = null;

    private $_client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->credentials === null) {
            if ($this->key === null) {
                throw new InvalidConfigException('The "key" property must be set.');
            }

            if ($this->secret === null) {
                throw new InvalidConfigException('The "secret" property must be set.');
            }
        }

        if ($this->bucket === null) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        parent::init();
    }

    /**
     * @return AwsS3Adapter
     */
    protected function prepareAdapter()
    {
        $config = [];

        if ($this->credentials === null) {
            $config['credentials'] = ['key' => $this->key, 'secret' => $this->secret];
        } else {
            $config['credentials'] = $this->credentials;
        }

        if ($this->region !== null) {
            $config['region'] = $this->region;
        }

        if ($this->pathStyleEndpoint === true) {
            $config['use_path_style_endpoint'] = true;
        }

        if ($this->endpoint !== null) {
            $config['endpoint'] = $this->endpoint;
        }

        $config['version'] = (($this->version !== null) ? $this->version : 'latest');

        $this->_client = new S3Client($config);

        return new AwsS3Adapter($this->_client, $this->bucket, $this->prefix, $this->options, $this->streamReads);
    }

    public function getUrl($path, $options = [])
    {
        $adapter = $this->getAdapter();
        if($this->replica){
            $adapter = $adapter->getSourceAdapter();
        }

        if($adapter instanceof CachedAdapter){
            $adapter = $adapter->getAdapter();
        }

        $key = $adapter->applyPathPrefix($path);
        
        if($this->publicUrl){
            return $this->publicUrl . DIRECTORY_SEPARATOR . $key;
        }

        return $this->_client->getObjectUrl($this->bucket,$key);
    }

    public function getPresignedUrl($path, $time = '+10 minutes')
    {
        // Get a command object from the client
        $command = $this->_client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $path
        ]);

        // Create a pre-signed URL for a request with duration of 10 miniutes
        $presignedRequest = $this->_client->createPresignedRequest($command, $time);

        return $presignedRequest->getUri();
    }
}
