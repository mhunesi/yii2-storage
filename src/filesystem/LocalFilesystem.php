<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * LocalFilesystem
 */
class LocalFilesystem extends Filesystem
{
    public ?string $path = null;
    public array $permission = [];
    public int $writeFlags = LOCK_EX;
    public int $linkHandling = LocalFilesystemAdapter::DISALLOW_LINKS;
    public ?string $publicUrl = null;

    public function init() : void
    {
        if ($this->path === null) {
            throw new InvalidConfigException('The "path" property must be set.');
        }

        $this->path = Yii::getAlias($this->path);

        parent::init();
    }

    protected function prepareAdapter() : FilesystemAdapter
    {
        return new LocalFilesystemAdapter(
			location: $this->path,
			visibility: PortableVisibilityConverter::fromArray($this->permission),
			writeFlags: $this->writeFlags,
			linkHandling: $this->linkHandling,
			mimeTypeDetector: null);
    }

    protected function getUrl($path, $options = []) : string
    {
        $uploadFolder = str_replace(Yii::getAlias('@app/web'), '', $this->path);

        return DIRECTORY_SEPARATOR . trim($uploadFolder . DIRECTORY_SEPARATOR . $path, '/');
    }
}
