<?php

namespace mhunesi\storage\filters;

use mhunesi\storage\base\Filter;

/**
 * Admin Module default Filter: Small Landscape (150x50)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SmallLandscape extends Filter
{
    public static function identifier()
    {
        return 'small-landscape';
    }

    public function name()
    {
        return 'Landscape small (150x50)';
    }

    public function prefix()
    {
        return 'sl_';
    }

    public function chain()
    {
        return [
            [self::EFFECT_THUMBNAIL, [
                'width' => 150,
                'height' => 50,
            ]],
        ];
    }
}
