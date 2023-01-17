<?php

namespace mhunesi\storage\actions;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use mhunesi\storage\Storage;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\base\InvalidConfigException;
use mhunesi\storage\models\StorageFile;

/**
 * (developer comment)
 *
 * @link http://www.mustafaunesi.com.tr/
 * @copyright Copyright (c) 2023 Polimorf IO
 * @product PhpStorm.
 * @author : Mustafa Hayri ÜNEŞİ <mhunesi@gmail.com>
 * @date: 13.01.2023
 * @time: 17:00
 */
class DownloadAction extends \yii\base\Action
{
    public function run($token,$filename)
    {
        /** @var Storage $storage */
        $storage = Yii::$app->storage;

        if(!$storage->downloadValidationKey){
            throw new InvalidConfigException("downloadValidationKey does not set.");
        }

        $json = Yii::$app->security->decryptByKey(base64_decode($token),$storage->downloadValidationKey);

        if(!$json){
            throw new NotFoundHttpException("File not found.");
        }

        $data = Json::decode($json);

        $where = [];

        array_map(function ($e) use($data,&$where){
            $where[$e] = $data[$e] ?? null;
        },['id', 'name_new_compound','hash_file','hash_name']);

        $file = StorageFile::find()->where($where)->one();

        if(!$file){
            throw new NotFoundHttpException("File not found.");
        }

        if(time() - $data['created_at'] >= $data['time']){
            throw new ForbiddenHttpException("File link expire.");
        }

        return Yii::$app->getResponse()->sendStreamAsFile($file->getStream(), $file->name_original, [
            'inline' => (bool)$file->inline_disposition,
            'mimeType' => $file->mime_type
        ]);
    }
}