<?php
/**
 * (developer comment)
 *
 * @link http://www.mustafaunesi.com.tr/
 * @copyright Copyright (c) 2023 Polimorf IO
 * @product PhpStorm.
 * @author : Mustafa Hayri ÜNEŞİ <mhunesi@gmail.com>
 * @date: 16.01.2023
 * @time: 14:04
 */

namespace mhunesi\storage\actions;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Util;
use mhunesi\storage\helpers\FileHelper;
use mhunesi\storage\Storage;
use Yii;
use yii\base\Action;
use yii\base\InvalidCallException;
use yii\web\Response;
use yii\web\UploadedFile;

class FileUploadAction extends Action
{
    /**
     * @var
     */
    public $path = null;

    public $folder = null;

    public $hidden = false;

    public $visibility = AdapterInterface::VISIBILITY_PUBLIC;

    public $use_strict = false;

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('file');

        try{
            /** @var Storage $storage */
            $storage = Yii::$app->storage->getStorage(Yii::$app->request->post('storageKey'));
        }catch (InvalidCallException $e){
            return ['upload' => false, 'message' => Yii::t('storage_upload','api_storage_not_found')];
        }

        $folderID = $this->use_strict ? $this->folder : Yii::$app->request->post('folderId',$this->folder);

        $isHidden = $this->use_strict ? $this->hidden : Yii::$app->request->post('isHidden',$this->hidden);

        $isVisibility = $this->use_strict ? $this->visibility : Yii::$app->request->post('isVisibility',$this->visibility);

        if($file){
            if ($file->error !== UPLOAD_ERR_OK) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => FileHelper::getUploadErrorMessage($file->error)];
            }
            try {

                if($this->path){
                    $fileName =  Util::normalizePath($this->path . DIRECTORY_SEPARATOR . $file->name);
                }else{
                    $fileName = $file->name;
                }

                $response = $storage->addFile($file->tempName, $fileName, $folderID, $isHidden, $isVisibility);
                if ($response) {
                    return [
                        'upload' => true,
                        'message' => Yii::t('storage_upload','api_storage_file_upload_success'),
                        'file' => $response,
                    ];
                } else {
                    Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                    return ['upload' => false, 'message' => Yii::t('storage_upload','api_storage_file_upload_folder_error')];
                }
            } catch (Exception $err) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => Yii::t('storage_upload','api_storage_file_upload_folder_error', ['error' => $err->getMessage()])];
            }
        }

        // If the files array is empty, this is an indicator for exceeding the upload_max_filesize from php ini or a wrong upload definition.
        Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
        return ['upload' => false, 'message' => FileHelper::getUploadErrorMessage(UPLOAD_ERR_NO_FILE)];
    }

}