<?php

namespace mhunesi\storage\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use mhunesi\storage\models\StorageFile;

/**
 * Process certain filters for an image/file.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class ImageFilterJob extends BaseObject implements JobInterface
{
    /**
     * @var integer The storage file id
     */
    public $fileId;

    /**
     * @var string|array The filter identifiers. This can be either an array or a string.
     */
    public $filterIdentifiers;

    /**
     * {@inheritDoc}
     */
    public function execute($queue)
    {
        if ($model = StorageFile::find()->where(['id' => $this->fileId])->one()) {
            foreach ((array) $this->filterIdentifiers as $identifier) {
                $model->applyFilter($identifier);
            }
        }
    }
}