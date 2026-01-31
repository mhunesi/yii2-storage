<?php


namespace mhunesi\storage\filesystem;

use \League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem as NativeFilesystem;
use League\Flysystem\FilesystemOperator;
use yii\base\Component;

/**
 * Filesystem
 *
 * @method bool fileExists(string $location)
 * @method bool directoryExists(string $location)
 * @method bool has(string $location)
 * @method string read(string $location)
 * @method resource readStream(string $location)
 * @method void write(string $location, string $contents, array $config = [])
 * @method void writeStream(string $location, $contents, array $config = [])
 * @method void delete(string $location)
 * @method void deleteDirectory(string $location)
 * @method void createDirectory(string $location, array $config = [])
 * @method void setVisibility(string $path, string $visibility)
 * @method string visibility(string $path)
 * @method string mimeType(string $path)
 * @method int lastModified(string $path)
 * @method int fileSize(string $path)
 * @method \League\Flysystem\DirectoryListing listContents(string $location, bool $deep = false)
 * @method void move(string $source, string $destination, array $config = [])
 * @method void copy(string $source, string $destination, array $config = [])
 * @method string checksum(string $path, array $config = [])
 * @method string publicUrl(string $path, array $config = [])
 * @method string temporaryUrl(string $path, \DateTimeInterface $expiresAt, array $config = [])
*/
abstract class Filesystem extends Component
{
    public array $config = [];

    protected FilesystemOperator $filesystem;

    protected FilesystemAdapter $sourceAdapter;

    public function init()
    {
        $this->sourceAdapter = $adapter = $this->prepareAdapter();
        $this->filesystem = new NativeFilesystem($adapter, $this->config);
    }

    abstract protected function prepareAdapter() : FilesystemAdapter;

    abstract protected function getUrl($path,$options = []) : string;

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters) : mixed
    {
        if(method_exists($this, $method)){
            return call_user_func_array([$this, $method], $parameters);
        }

        return call_user_func_array([$this->filesystem, $method], $parameters);
    }

    public function getFilesystem() : FilesystemOperator
    {
        return $this->filesystem;
    }
}
