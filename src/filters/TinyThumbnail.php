<?php

namespace mhunesi\storage\filters;

use mhunesi\storage\base\Filter;

/**
 * Admin Module default Filter: Tiny Thumbnail (40xnull)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class TinyThumbnail extends Filter
{
    public static function identifier()
    {
        return 'tiny-thumbnail';
    }

    public function name()
    {
        return 'Thumbnail tiny (40xnull)';
    }

    public function prefix()
    {
        return 'tt_';
    }

    public function chain()
    {
        return [
            [self::EFFECT_THUMBNAIL, [
                'width' => 40,
                'height' => null,
            ]],
        ];
    }
}
