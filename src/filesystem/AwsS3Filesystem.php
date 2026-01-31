<?php


namespace mhunesi\storage\filesystem;

use Aws\S3\S3Client;
use League\Flysystem\PathPrefixer;
use yii\base\InvalidConfigException;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * AwsS3Filesystem
 */
class AwsS3Filesystem extends Filesystem
{
    public ?string $key = null;
    public ?string $secret = null;
    public ?string $region = null;
    public ?string $baseUrl = null;
    public ?string $version = null;
    public ?string $bucket = null;
    public ?string $prefix = null;
    public bool $pathStyleEndpoint = false;
    public array $options = [];
    public bool $streamReads = false;
    public ?string $endpoint = null;
    public mixed $credentials = null;
    public ?string $publicUrl = null;

    private ?S3Client $_client = null;

    public function init() : void
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

    protected function prepareAdapter() : AwsS3V3Adapter
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

        return new AwsS3V3Adapter(
			client: $this->_client,
			bucket: $this->bucket,
			prefix: $this->prefix,
			options: $this->options,
			streamReads: $this->streamReads);
    }

    public function getUrl($path, $options = []) : string
    {
		if($this->prefix){
			$pathPrefixer = new PathPrefixer($this->prefix);
			$path = $pathPrefixer->prefixPath($path);
		}

        if($this->publicUrl){
            return $this->publicUrl . DIRECTORY_SEPARATOR . $path;
        }

        return $this->_client->getObjectUrl($this->bucket,$path);
    }

    public function getPresignedUrl(string $path, string $time = '+10 minutes') : string
    {
        $command = $this->_client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $path
        ]);

        $presignedRequest = $this->_client->createPresignedRequest($command, $time);

        return (string) $presignedRequest->getUri();
    }
}
