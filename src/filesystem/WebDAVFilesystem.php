<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use yii\base\InvalidConfigException;

/**
 * WebDAVFilesystem

 */
class WebDAVFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $baseUri;
    /**
     * @var string
     */
    public $userName;
    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $proxy;
    /**
     * @var integer
     */
    public $authType;
    /**
     * @var integer
     */
    public $encoding;
    /**
     * @var string|null
     */
    public $prefix;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->baseUri === null) {
            throw new InvalidConfigException('The "baseUri" property must be set.');
        }

        parent::init();
    }

    /**
     * @return WebDAVAdapter
     */
    protected function prepareAdapter()
    {
        $config = [];

        foreach ([
            'baseUri',
            'userName',
            'password',
            'proxy',
            'authType',
            'encoding',
        ] as $name) {
            if ($this->$name !== null) {
                $config[$name] = $this->$name;
            }
        }

        return new WebDAVAdapter(
            new Client($config),
            $this->prefix
        );
    }
}
