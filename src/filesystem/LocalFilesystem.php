<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Util;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * LocalFilesystem
 */
class LocalFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $path;

    public $permission = [];

    public $writeFlags = LOCK_EX;

    public $linkHandling = Local::DISALLOW_LINKS;

    public $publicUrl = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->path === null) {
            throw new InvalidConfigException('The "path" property must be set.');
        }

        $this->path = Yii::getAlias($this->path);

        parent::init();
    }

    /**
     * @return Local
     */
    protected function prepareAdapter()
    {
        return new Local($this->path, $this->writeFlags, $this->linkHandling, $this->permission);
    }

    protected function getUrl($path, $options = [])
    {
        $uploadFolder = str_replace(Yii::getAlias('@app/web'),'',$this->path);

        return DIRECTORY_SEPARATOR . Util::normalizePath($uploadFolder . DIRECTORY_SEPARATOR . $path);
    }
}
