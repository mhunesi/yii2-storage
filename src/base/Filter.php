<?php

namespace mhunesi\storage\base;

use Imagine\Image\ImageInterface;
use yii\helpers\Json;
use yii\base\Exception;
use yii\base\BaseObject;
use mhunesi\storage\models\StorageEffect;
use mhunesi\storage\models\StorageFilter;
use mhunesi\storage\models\StorageFilterChain;
use yii\imagine\Image;

/**
 * Base class for all storage component filters.
 *
 * The available effects for the chain are stored in the database, here the default effects which are
 * provided when installing luya:
 *
 * + thumbnail: width, height, mode, saveOptions
 * + crop: width, height, saveOptions
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Filter extends BaseObject implements FilterInterface
{
    /**
     * Understandable Name expression for the effect.
     *
     * @return string A string containing the name to be listed in the admin area.
     */
    abstract public function name();

    abstract public function prefix();

    /**
     * An array with represents the chain effects and the value of the defined effects.
     *
     * @return array Example response for chain() method:
     *
     * ```php
     * return [
     *     [self::EFFECT_THUMBNAIL, [
     *         'width' => 100,
     *         'height' => 100,
     *     ]],
     * ];
     * ```
     */
    abstract public function chain();

    private function applyFilter($image,$effectIdentifier,$effectParams)
    {
        if ($effectIdentifier == self::EFFECT_CROP) {
            return Image::crop($image, $effectParams['width'], $effectParams['height']);
        }
        elseif ($effectIdentifier == FilterInterface::EFFECT_THUMBNAIL) {
            return Image::thumbnail($image, $effectParams['width'], $effectParams['height'], ($effectParams['mode'] ?? ImageInterface::THUMBNAIL_FLAG_NOCLONE));
        }
        elseif ($effectIdentifier == FilterInterface::EFFECT_WATERMARK) {
            return Image::watermark($image, $effectParams['image'], $effectParams['start']);
        }
        elseif ($effectIdentifier == FilterInterface::EFFECT_TEXT) {
            return Image::text($image, $effectParams['text'], $effectParams['fontFile'], $effectParams['start']);
        }

        return $image;
    }

    public function applyFilterChain($resource, $fileSavePath = null,$saveOptions = [])
    {
        $image = Image::getImagine()->read($resource);

        foreach ($this->chain() as $chainRow) {

            $effectIdentifier = $chainRow[0];
            $effectParams = $chainRow[1];

            $image = $this->applyFilter($image,$effectIdentifier,$effectParams);
        }

        $filename = \Yii::getAlias('@runtime/storage/') .'temp_'.microtime().'.jpg';

        // auto rotate & save
        $image = Image::autoRotate($image)
            ->save($filename);

        return $filename;

    }
}
