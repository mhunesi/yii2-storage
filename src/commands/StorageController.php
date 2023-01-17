<?php

namespace mhunesi\storage\commands;

use Yii;
use mhunesi\storage\models\StorageFile;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * (developer comment)
 *
 * @link http://www.mustafaunesi.com.tr/
 * @copyright Copyright (c) 2023 Polimorf IO
 * @product PhpStorm.
 * @author : Mustafa Hayri ÜNEŞİ <mhunesi@gmail.com>
 * @date: 13.01.2023
 * @time: 16:10
 */
class StorageController extends \yii\console\Controller
{
    public function actionThumbnails(array $filters)
    {
        $files = StorageFile::find()->where(['parent_id' => null])->andWhere(['mime_type' => Yii::$app->storage->imageMimeTypes,'is_deleted' => 0])->all();

        $filterCount = count($filters);
        $fileCount = count($files);

        if(Console::confirm("{$fileCount} files and {$filterCount} filter. Do you want apply?")){
            Console::startProgress(0, $filterCount, 'Counting objects: ', false);
            foreach ($files as $k => $file) {
                foreach ($filters as $filter) {
                    $file->applyFilter($filter);
                }

                Console::updateProgress($k, $fileCount);
            }
            Console::endProgress("filter applied." . PHP_EOL);
        }

        return ExitCode::OK;
    }

    public function actionRemoveImages()
    {
        $files = StorageFile::find()->where(['IS NOT','parent_id',null])->andWhere(['mime_type' => Yii::$app->storage->imageMimeTypes,'is_deleted' => 0])->all();

        $count = count($files);

        if($count == 0){
            Console::output("Image not found.");
            return ExitCode::OK;
        }

        if(Console::confirm("{$count} images found. Do you want deleted this images?")){

            Console::startProgress(0, $count, 'Counting objects: ', false);
            foreach ($files as $k => $file) {
                $file->delete();
                Console::updateProgress($k, $count);
            }
            Console::endProgress("image removed." . PHP_EOL);
        }

        return ExitCode::OK;
    }

    public function actionRemoveAll()
    {
        $files = StorageFile::find()->where(['is_deleted' => 0])->all();

        $count = count($files);

        if($count == 0){
            Console::output("File not found.");
            return ExitCode::OK;
        }

        if(Console::confirm("{$count} files found. Do you want delete?")){

            Console::startProgress(0, $count, 'Counting objects: ', false);
            foreach ($files as $k => $file) {
                $file->delete();
                Console::updateProgress($k, $count);
            }
            Console::endProgress("file removed." . PHP_EOL);
        }

        return ExitCode::OK;
    }
}