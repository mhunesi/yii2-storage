<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Azure\AzureAdapter;
use WindowsAzure\Common\ServicesBuilder;
use yii\base\InvalidConfigException;

/**
 * AzureFilesystem
*/
class AzureFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $accountName;
    /**
     * @var string
     */
    public $accountKey;
    /**
     * @var string
     */
    public $container;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->accountName === null) {
            throw new InvalidConfigException('The "accountName" property must be set.');
        }

        if ($this->accountKey === null) {
            throw new InvalidConfigException('The "accountKey" property must be set.');
        }

        if ($this->container === null) {
            throw new InvalidConfigException('The "container" property must be set.');
        }

        parent::init();
    }

    /**
     * @return AzureAdapter
     */
    protected function prepareAdapter()
    {
        return new AzureAdapter(
            ServicesBuilder::getInstance()->createBlobService(sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
                base64_encode($this->accountName),
                base64_encode($this->accountKey)
            )),
            $this->container
        );
    }

    protected function getUrl($path, $options = [])
    {
        // TODO: Implement getUrl() method.
        return $path;
    }
}
