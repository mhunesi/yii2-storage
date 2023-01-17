<?php

namespace mhunesi\storage\filters;

use mhunesi\storage\base\Filter;

/**
 * Admin Module default Filter: Small Thumbail (100xnull)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SmallThumbnail extends Filter
{
    public static function identifier()
    {
        return 'small-thumbnail';
    }

    public function name()
    {
        return 'Thumbnail small (100xnull)';
    }

    public function prefix()
    {
        return 'st_';
    }

    public function chain()
    {
        return [
            [self::EFFECT_THUMBNAIL, [
                'width' => 100,
                'height' => null,
            ]],
        ];
    }
}
