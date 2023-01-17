<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Util;
use Yii;
use yii\base\InvalidConfigException;

/**
 * SftpFilesystem

 */
class SftpFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $host;
    /**
     * @var string
     */
    public $port;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;
    /**
     * @var integer
     */
    public $timeout;
    /**
     * @var string
     */
    public $root;
    /**
     * @var string
     */
    public $privateKey;
    /**
     * @var integer
     */
    public $permPrivate;
    /**
     * @var integer
     */
    public $permPublic;
    /**
     * @var integer
     */
    public $directoryPerm;

    /**
     * @var string
     */
    public $publicUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->host === null) {
            throw new InvalidConfigException('The "host" property must be set.');
        }

        if ($this->username === null) {
            throw new InvalidConfigException('The "username" property must be set.');
        }

        if ($this->password === null && $this->privateKey === null) {
            throw new InvalidConfigException('Either "password" or "privateKey" property must be set.');
        }

        if ($this->root !== null) {
            $this->root = Yii::getAlias($this->root);
        }

        parent::init();
    }

    /**
     * @return SftpAdapter
     */
    protected function prepareAdapter()
    {
        $config = [];

        foreach ([
            'host',
            'port',
            'username',
            'password',
            'timeout',
            'root',
            'privateKey',
            'permPrivate',
            'permPublic',
            'directoryPerm',
        ] as $name) {
            if ($this->$name !== null) {
                $config[$name] = $this->$name;
            }
        }

        return new SftpAdapter($config);
    }

    /**
     * @param $path
     * @return string
     */
    public function getUrl($path, $options = [])
    {
        return Util::normalizePath($this->publicUrl . DIRECTORY_SEPARATOR . $path);
    }
}
