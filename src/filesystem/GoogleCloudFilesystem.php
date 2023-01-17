<?php


namespace mhunesi\storage\filesystem;

use Google\Cloud\Storage\StorageClient;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use yii\base\InvalidConfigException;

/**
 * GoogleCloudFilesystem
 */
class GoogleCloudFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $projectId;
    /**
     * @var string
     */
    public $keyFilePath;
    /**
     * @var string
     */
    public $bucket;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->projectId === null) {
            throw new InvalidConfigException('The "projectId" property must be set.');
        }

        if ($this->keyFilePath === null) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        if ($this->bucket === null) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        parent::init();
    }

    /**
     * @return GoogleStorageAdapter
     */
    protected function prepareAdapter()
    {
        $config = [
                'projectId' => $this->projectId,
                'keyFilePath' => \Yii::getAlias($this->keyFilePath),
        ];

        $client = new StorageClient($config);
        $bucket = $client->bucket($this->bucket);
        return  new GoogleStorageAdapter($client, $bucket);
    }

    protected function getUrl($path, $options = [])
    {
        // TODO: Implement getUrl() method.
        return $path;
    }
}
