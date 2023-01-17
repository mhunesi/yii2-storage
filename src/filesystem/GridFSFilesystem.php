<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\GridFS\GridFSAdapter;
use MongoClient;
use yii\base\InvalidConfigException;

/**
 * GridFSFilesystem
 */
class GridFSFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $server;
    /**
     * @var string
     */
    public $database;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->server === null) {
            throw new InvalidConfigException('The "server" property must be set.');
        }

        if ($this->database === null) {
            throw new InvalidConfigException('The "database" property must be set.');
        }

        parent::init();
    }

    /**
     * @return GridFSAdapter
     */
    protected function prepareAdapter()
    {
        return new GridFSAdapter((new MongoClient($this->server))->selectDB($this->database)->getGridFS());
    }

    protected function getUrl($path, $options = [])
    {
        // TODO: Implement getUrl() method.
        return $path;
    }
}
