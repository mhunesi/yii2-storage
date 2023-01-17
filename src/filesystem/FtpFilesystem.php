<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Util;
use Yii;
use yii\base\InvalidConfigException;

/**
 * FtpFilesystem
*/
class FtpFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $host;
    /**
     * @var integer
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
     * @var boolean
     */
    public $ssl;
    /**
     * @var integer
     */
    public $timeout;
    /**
     * @var string
     */
    public $root;
    /**
     * @var integer
     */
    public $permPrivate;
    /**
     * @var integer
     */
    public $permPublic;
    /**
     * @var boolean
     */
    public $passive;
    /**
     * @var integer
     */
    public $transferMode;
    /**
     * @var bool
     */
    public $enableTimestampsOnUnixListings = false;


    public $publicUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->host === null) {
            throw new InvalidConfigException('The "host" property must be set.');
        }

        if ($this->publicUrl === null) {
            throw new InvalidConfigException('The "publicUrl" property must be set.');
        }

        if ($this->root !== null) {
            $this->root = Yii::getAlias($this->root);
        }

        parent::init();
    }

    /**
     * @return Ftp
     */
    protected function prepareAdapter()
    {
        $config = [];

        foreach ([
            'host',
            'port',
            'username',
            'password',
            'ssl',
            'timeout',
            'root',
            'permPrivate',
            'permPublic',
            'passive',
            'transferMode',
            'enableTimestampsOnUnixListings',
        ] as $name) {
            if ($this->$name !== null) {
                $config[$name] = $this->$name;
            }
        }

        return new Ftp($config);
    }

    /**
     * @param $path
     * @return string
     */
    public function getUrl($path, $options = [])
    {
        return $this->publicUrl . DIRECTORY_SEPARATOR . $path;
    }
}
