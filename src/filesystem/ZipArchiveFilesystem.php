<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * ZipArchiveFilesystem

 */
class ZipArchiveFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $path;
    /**
     * @var string|null
     */
    public $prefix;

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
     * @return ZipArchiveAdapter
     */
    protected function prepareAdapter()
    {
        return new ZipArchiveAdapter(
            $this->path,
            null,
            $this->prefix
        );
    }

    protected function getUrl($path, $options = [])
    {
        // TODO: Implement getUrl() method.
        return $path;
    }
}
