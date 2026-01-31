<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use Yii;
use yii\base\InvalidConfigException;

/**
 * SftpFilesystem

 */
class SftpFilesystem extends Filesystem
{
    public ?string $host = null;
    public int $port = 22;
    public ?string $username = null;
    public ?string $password = null;
    public int $timeout = 10;
    public ?string $root = null;
    public ?string $privateKey = null;
    public ?int $permPrivate = null;
    public ?int $permPublic = null;
    public ?int $directoryPerm = null;
    public ?string $publicUrl = null;

    public function init() : void
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

    protected function prepareAdapter() : FilesystemAdapter
    {
        $provider = new SftpConnectionProvider(
            host: $this->host,
            username: $this->username,
            password: $this->password,
            privateKey: $this->privateKey,
            port: $this->port,
            timeout: $this->timeout
        );

        return new SftpAdapter($provider, $this->root ?? '/');
    }

    public function getUrl($path, $options = []) : string
    {
        return trim($this->publicUrl . '/' . $path, '/');
    }
}
