<?php

namespace mhunesi\storage\filters;

use mhunesi\storage\base\Filter;

/**
 * Admin Module default Filter: Medium Crop (300x300)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class MediumCrop extends Filter
{
    public static function identifier()
    {
        return 'medium-crop';
    }

    public function name()
    {
        return 'Crop medium (300x300)';
    }

    public function prefix()
    {
        return 'mc_';
    }

    public function chain()
    {
        return [
            [self::EFFECT_THUMBNAIL, [
                'width' => 300,
                'height' => 300,
            ]],
        ];
    }
}
