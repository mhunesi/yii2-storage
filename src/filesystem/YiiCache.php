<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Cached\Storage\AbstractCache;
use yii\caching\Cache;

/**
 * YiiCache

 */
class YiiCache extends AbstractCache
{
    /**
     * @var Cache
     */
    protected $yiiCache;
    /**
     * @var string
     */
    protected $key;
    /**
     * @var integer
     */
    protected $duration;

    /**
     * @param Cache $yiiCache
     * @param string $key
     * @param integer $duration
     */
    public function __construct(Cache $yiiCache, $key = 'flysystem', $duration = 0)
    {
        $this->yiiCache = $yiiCache;
        $this->key = $key;
        $this->duration = $duration;
    }

    /**
     * @inheritdoc
     */
    public function load()
    {
        $contents = $this->yiiCache->get($this->key);

        if ($contents !== false) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $this->yiiCache->set($this->key, $this->getForStorage(), $this->duration);
    }
}
