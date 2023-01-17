<?php

namespace mhunesi\storage\events;

use mhunesi\storage\models\StorageFile;

/**
 * (developer comment)
 *
 * @link http://www.mustafaunesi.com.tr/
 * @copyright Copyright (c) 2022 Polimorf IO
 * @product PhpStorm.
 * @author : Mustafa Hayri ÜNEŞİ <mhunesi@gmail.com>
 * @date: 14.12.2022
 * @time: 14:35
 */
class FileEvent extends \yii\base\Event
{
    public StorageFile $file;

}