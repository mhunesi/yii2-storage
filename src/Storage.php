<?php

namespace mhunesi\storage;


use League\Flysystem\AdapterInterface;
use League\Flysystem\MountManager;
use League\Flysystem\Replicate\ReplicateAdapter;
use League\Flysystem\Util;
use mhunesi\storage\base\Filter;
use mhunesi\storage\models\StorageFile;
use mhunesi\storage\events\FileEvent;
use mhunesi\storage\jobs\ImageFilterJob;
use mhunesi\storage\models\StorageFolder;
use Yii;
use yii\base\Exception;
use mhunesi\storage\helpers\FileHelper;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use League\Flysystem\FilesystemInterface;
use mhunesi\storage\filesystem\Filesystem;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * @property Filesystem $fileSystem
 * @property MountManager $mountManager
 */
class Storage extends \yii\base\Component
{
	/**
	 * @var string This event is triggered when the storage file model is updating, for example when change the disposition.
	 * @since 2.0.0
	 */
	const FILE_UPDATE_EVENT = 'onFileUpdate';

	/**
	 * @var string This event is triggered when a new file is uploaded to the file system.
	 * @since 2.0.0
	 */
	const FILE_SAVE_EVENT = 'onFileSave';

	/**
	 * @var Filesystem[]
	 */
	private array $_storages = [];

	/**
	 * @var Filesystem
	 */
	private $_fs;

	public const FILE_SYSTEMS = [
		'mhunesi\storage\filesystem\AwsS3Filesystem',
		'mhunesi\storage\filesystem\AzureFilesystem',
		'mhunesi\storage\filesystem\DropboxFilesystem',
		'mhunesi\storage\filesystem\FtpFilesystem',
		'mhunesi\storage\filesystem\GoogleCloudFilesystem',
		'mhunesi\storage\filesystem\LocalFilesystem',
		'mhunesi\storage\filesystem\RackspaceFilesystem',
		'mhunesi\storage\filesystem\SftpFilesystem',
		'mhunesi\storage\filesystem\WebDAVFilesystem'
	];

	/**
	 * @var array
	 */
	public $filters = [];

	public string $defaultStorage;

	public string $currentStorageKey;

	/**
	 * @var array
	 */
	public array $storages = [];

	public array $dangerousMimeTypes = [
		'application/x-msdownload',
		'application/x-msdos-program',
		'application/x-msdos-windows',
		'application/x-download',
		'application/bat',
		'application/x-bat',
		'application/com',
		'application/x-com',
		'application/exe',
		'application/x-exe',
		'application/x-winexe',
		'application/x-winhlp',
		'application/x-winhelp',
		'application/x-javascript',
		'application/hta',
		'application/x-ms-shortcut',
		'application/octet-stream',
		'vms/exe',
		'text/javascript',
		'text/scriptlet',
		'text/x-php',
		'text/plain',
		'application/x-spss',
		'image/svg+xml',
	];

	/**
	 * @var array The extension which will be rejected.
	 */
	public array $dangerousExtensions = [
		'html',
		'php',
		'phtml',
		'php3',
		'exe',
		'bat',
		'js',
	];

	/**
	 * @var array a list of mime types which are indicating images
	 * @since 1.2.2.1
	 */
	public array $imageMimeTypes = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/jpg',
		'image/webp',
	];

	/**
	 * @var boolean Whether secure file upload is enabled or not. If enabled dangerous mime types and extensions will
	 * be rejected and the file mime type needs to be verified by phps `fileinfo` extension.
	 */
	public bool $secureFileUpload = true;

	/**
	 * @var array The mime types inside this array are whitelistet and will be stored whether validation failes or not. For example if mime
	 * type 'text/plain' is given for a 'csv' extension, the valid extensions would be 'txt' or 'log', this would throw an exception, therefore
	 * you can whitelist the 'text/plain' mime type. This can be usefull when uploading csv files.
	 * @since 1.1.0
	 */
	public array $whitelistMimeTypes = [];

	/**
	 * @var array An array with extensions which are whitelisted. This can be very dangerous as it will skip the check whether the mime type is
	 * matching the extension types. If an extensions in {{$dangerousExtensions}} and {{$whitelistExtensions}} it will still throw an exception as
	 * {{$dangerousExtensions}} take precedence over {{$$whitelistExtensions}}.
	 * @since 1.2.2
	 */
	public array $whitelistExtensions = [];


	/**
	 * @var boolean When enabled, the filters in the {{luya\admin\storage\BaseFileSystemStorage::$queueFiltersList}} will be applied to the uploaded file if the file is an image. We
	 * recommend you turn this on, only when using the `queue/listen` command see [[app-queue.md]], because the user needs to wait until the queue job is processed
	 * in the admin ui.
	 * @since 4.0.0
	 */
	public bool $queueFilters = false;

	/**
	 * @var array If {{luya\admin\storage\BaseFileSystemStorage::$queueFilters}} is enabled, the following image filters will be processed. We recommend
	 * to add the default filters which are used in the admin ui (for file manager thumbnails). Therefore those are default values `['tiny-crop', 'medium-thumbnail']`.
	 * @since 4.0.0
	 */
	public array $queueFiltersList = ['tiny-crop', 'medium-thumbnail'];

	/**
	 * @var array If the storage system pushed any jobs into the queue, this array holds the queue job ids.
	 */
	public array $queueJobIds = [];

	/**
	 * @var bool
	 */
	public bool $fileDefaultInlineDisposition = true;

	/**
	 * @var string
	 */
	public $downloadValidationKey;

	/**
	 * @var string
	 */
	public $downloadUrl = '/site/download';

	/**
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		parent::init();

		$this->registerTranslations();

		if (!$this->defaultStorage) {
			throw new InvalidConfigException("defaultStorage must be set.");
		}

		foreach ($this->storages as $identifier => $storage) {

			if (isset($storage['replica'])) {
				if (is_string($storage['replica']) && isset($this->storages[$storage['replica']])) {
					$storage['replica'] = Yii::createObject($this->storages[$storage['replica']]);
				} elseif (is_array($storage['replica'])) {
					$storage['replica'] = Yii::createObject($storage['replica']);
				} else {
					throw new InvalidConfigException("replica be string or array");
				}
			}

			$this->_storages[$identifier] = Yii::createObject($storage);
		}

		$this->getStorage($this->defaultStorage);
	}

	/**
	 * @return void
	 */
	private function registerTranslations()
	{
		$i18n = Yii::$app->i18n;
		$i18n->translations['storage_*'] = [
			'class' => 'yii\i18n\PhpMessageSource',
			'sourceLanguage' => 'en-US',
			'basePath' => '@mhunesi/storage/messages',
			'fileMap' => [
				'storage_file' => 'storage_file.php',
			],
		];
	}

	/**
	 * @param $identifier
	 * @return Storage
	 * @throws InvalidCallException
	 */
	public function getStorage($identifier)
	{
		if (isset($this->_storages[$identifier])) {
			$this->_fs = $this->_storages[$identifier];
			$this->currentStorageKey = $identifier;

			return $this;
		}

		throw new InvalidCallException("{$identifier} storage not found.");
	}

	/**
	 * @return Filesystem
	 */
	public function getFileSystem()
	{
		return $this->_fs;
	}

	/**
	 * @return MountManager
	 */
	public function getMountManager()
	{
		$fileSystems = [];

		foreach ($this->_storages as $k => $storage) {
			$fileSystems[$k] = $storage->getFilesystem();
		}

		return new MountManager($fileSystems);
	}

	/**
	 * @param $filterId
	 * @return Filter|Filter[]
	 * @throws InvalidConfigException
	 */
	public function getFilters($filter_id = null)
	{
		$filters = ArrayHelper::merge($this->filters, [
			'mhunesi\storage\filters\LargeCrop',
			'mhunesi\storage\filters\LargeThumbnail',
			'mhunesi\storage\filters\MediumCrop',
			'mhunesi\storage\filters\MediumThumbnail',
			'mhunesi\storage\filters\SmallCrop',
			'mhunesi\storage\filters\SmallLandscape',
			'mhunesi\storage\filters\SmallThumbnail',
			'mhunesi\storage\filters\TinyCrop',
			'mhunesi\storage\filters\TinyThumbnail',
		]);

		$items = [];

		foreach ($filters as $filter) {
			$filter = Yii::createObject(['class' => $filter]);
			if ($filter instanceof Filter) {
				$id = call_user_func([$filter, 'identifier']);
				$items[$id] = $filter;
			}

		}

		return $filter_id ? $items[$filter_id] : null;
	}

	/**
	 * @param string|resource $fileSource
	 * @param $fileName
	 * @param $folderId
	 * @param bool $isHidden
	 * @return false|StorageFile
	 * @throws Exception
	 */
	public function addFile($fileSource, $fileName, $folderId = null, $isHidden = false, $visibility = AdapterInterface::VISIBILITY_PUBLIC)
	{
		$pathInfo = Util::pathinfo($fileName);

		$dirname = Util::normalizePath($pathInfo['dirname']);

		$fileData = $this->ensureFileUpload($fileSource, $fileName);

		$fileHash = FileHelper::md5sum($fileSource);

		$newName = implode('.', [$fileData['secureFileName'] . '_' . $fileData['hashName'], $fileData['extension']]);

		try {

			$stream = fopen($fileSource, 'r+');

			if ($dirname !== "") {
				$newPath = $dirname . DIRECTORY_SEPARATOR . $newName;
			} else {
				$newPath = $newName;
			}

			$this->_fs->writeStream($newPath, $stream, [
				'visibility' => $visibility
			]);

			fclose($stream);

		} catch (\League\Flysystem\FilesystemException|\League\Flysystem\UnableToWriteFile|\Throwable $exception) {
			return false;
		}

		$adapter = $this->_fs->getAdapter();

		$adapter = $adapter instanceof ReplicateAdapter ? $adapter->getSourceAdapter() : $adapter;

		$model = new StorageFile();

		$model->setAttributes([
			'storage_key' => $this->currentStorageKey,
			'name_original' => $pathInfo['basename'],
			'name_new' => $fileData['secureFileName'],
			'name_new_compound' => $newName,
			'mime_type' => $fileData['mimeType'],
			'extension' => $fileData['extension'],
			'folder_id' => $folderId,
			'hash_file' => $fileHash,
			'hash_name' => $fileData['hashName'],
			'is_hidden' => $isHidden ? true : false,
			'is_visibility' => ($visibility === AdapterInterface::VISIBILITY_PUBLIC ? 0 : 1),
			'is_deleted' => false,
			'file_size' => $fileData['fileSize'],
			'path_prefix' => $this->_fs->hasProperty('prefix') ? $this->_fs->prefix : null,
			'file_path' => $dirname,
			'caption' => null,
			'inline_disposition' => (int)$this->fileDefaultInlineDisposition,
		]);

		if ($model->validate()) {

			if ($model->isImage) {
				$resolution = FileHelper::getImageResolution($fileSource, false);
				$model->resolution_height = $resolution['height'];
				$model->resolution_width = $resolution['width'];
			}

			if ($model->save()) {
				if ($model->isImage && $this->queueFilters && Yii::$app->get('queue', false)) {
					$this->queueJobIds[] = Yii::$app->queue->push(new ImageFilterJob(['fileId' => $model->id, 'filterIdentifiers' => $this->queueFiltersList]));
				}
				$this->trigger(self::FILE_SAVE_EVENT, new FileEvent(['file' => $model]));
				return $model;
			}
		}

		return false;
	}

	/**
	 * @param $name
	 * @param $parent_id
	 * @return StorageFolder|null
	 */
	public function addFolder($name, $parent_id = null)
	{
		$model = new StorageFolder([
			'name' => $name,
			'parent_id' => $parent_id
		]);

		return $model->save() ? $model : null;
	}

	public function applyFilter($file_id, $filter_id)
	{
		$image = StorageFile::find()->where(['parent_id' => $file_id, 'filter_identifier' => $filter_id, 'is_deleted' => 0])->one();

		if ($image) {
			return $image;
		}

		$file = StorageFile::find()->where(['id' => $file_id, 'is_deleted' => 0])->one();

		if (!$file) {
			return false;
		}

		$filter = $this->getFilters($filter_id);

		$path = ($file->file_path ? $file->file_path . DIRECTORY_SEPARATOR : null);

		$tempFile = $filter->applyFilterChain($file->getStream());

		$model = new StorageFile();

		$resolution = FileHelper::getImageResolution($tempFile, false);

		$model->setAttributes([
			'storage_key' => $file->storage_key,
			'name_original' => $filter->prefix() . $file->name_original,
			'name_new' => $file->name_new,
			'name_new_compound' => $filter->prefix() . $file->name_new_compound,
			'mime_type' => $file->mime_type,
			'extension' => $file->extension,
			'folder_id' => $file->folder_id,
			'hash_file' => FileHelper::md5sum($tempFile),
			'hash_name' => $file->hash_name,
			'is_hidden' => $file->is_hidden,
			'is_visibility' => $file->is_visibility,
			'file_size' => filesize($tempFile),
			'path_prefix' => $file->path_prefix,
			'file_path' => $file->file_path,
			'inline_disposition' => $file->inline_disposition,
			'parent_id' => $file->id,
			'filter_identifier' => $filter->identifier(),
			'resolution_height' => $resolution['height'],
			'resolution_width' => $resolution['width'],
		]);

		try {

			$stream = fopen($tempFile, 'r+');

			$this->_fs->writeStream($model->path, $stream, [
				'visibility' => $file->is_visibility == 0 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE
			]);

			fclose($stream);

		} catch (\League\Flysystem\FilesystemException|\League\Flysystem\UnableToWriteFile|\Throwable $exception) {
			return false;
		}

		$model->save();

		FileHelper::unlink($tempFile);

		return $model;
	}

	/**
	 * Ensure a file uploads and return relevant file infos.
	 *
	 * @param string $fileSource The file on the server ($_FILES['tmp'])
	 * @param string $fileName Original upload name of the file ($_FILES['name'])
	 * @return array Returns an array with the following KeywordPatch
	 * + fileInfo:
	 * + mimeType:
	 * + fileName:
	 * + secureFileName: The file name with all insecure chars removed
	 * + fileSource:
	 * + extension: jpg, png, etc.
	 * + hashName: a short hash name for the given file, not the md5 sum.
	 * @throws Exception
	 */
	public function ensureFileUpload($fileSource, $fileName): array
	{
		// throw exception if source or name is empty
		if (empty($fileSource) || empty($fileName)) {
			throw new Exception("Filename and source can not be empty.");
		}
		// if filename is blob, its a paste event from the browser, therefore generate the filename from the file source.
		// @TODO: move out of ensureFileUpload
		if ($fileName == 'blob') {
			$ext = FileHelper::getExtensionsByMimeType(FileHelper::getMimeType($fileSource));
			$fileName = 'paste-' . date("Y-m-d-H-i") . '.' . $ext[0];
		}
		// get file informations from the name
		$fileInfo = FileHelper::getFileInfo($fileName);
		// get the mimeType from the fileSource, if $secureFileUpload is disabled, the mime type will be extracted from the file extensions
		// instead of using the fileinfo extension, therefore this is not recommend.
		$mimeType = FileHelper::getMimeType($fileSource, null, !$this->secureFileUpload);
		// empty mime type indicates a wrong file upload.
		if (empty($mimeType)) {
			throw new Exception("Unable to find mimeType for the given file, make sure the php extension 'fileinfo' is installed.");
		}

		$extensionsFromMimeType = FileHelper::getExtensionsByMimeType($mimeType);

		if (empty($extensionsFromMimeType) && empty($this->whitelistExtensions)) {
			throw new Exception("Unable to find extension for given mimeType \"{$mimeType}\" or it contains insecure data.");
		}

		if (!empty($this->whitelistExtensions)) {
			$extensionsFromMimeType = array_merge($extensionsFromMimeType, $this->whitelistExtensions);
		}

		// check if the file extension is matching the entries from FileHelper::getExtensionsByMimeType array.
		if (!in_array($fileInfo->extension, $extensionsFromMimeType) && !in_array($mimeType, $this->whitelistMimeTypes)) {
			throw new Exception("The given file extension \"{$fileInfo->extension}\" for file with mimeType \"{$mimeType}\" is not matching any valid extension: " . VarDumper::dumpAsString($extensionsFromMimeType) . ".");
		}

		foreach ($extensionsFromMimeType as $extension) {
			if (in_array($extension, $this->dangerousExtensions)) {
				throw new Exception("The file extension '{$extension}' seems to be dangerous and can not be stored.");
			}
		}

		// check whether a mimetype is in the dangerousMimeTypes list and not whitelisted in whitelistMimeTypes.
		if (in_array($mimeType, $this->dangerousMimeTypes) && !in_array($mimeType, $this->whitelistMimeTypes)) {
			throw new Exception("The file mimeType '{$mimeType}' seems to be dangerous and can not be stored.");
		}

		return [
			'fileInfo' => $fileInfo,
			'mimeType' => $mimeType,
			'fileName' => $fileName,
			'secureFileName' => Inflector::slug(str_replace('_', '-', $fileInfo->name), '-'),
			'fileSource' => $fileSource,
			'fileSize' => filesize($fileSource),
			'extension' => $fileInfo->extension,
			'hashName' => FileHelper::hashName($fileName),
		];
	}

	/**
	 * @param $fileId
	 * @return StorageFile|null
	 */
	public function getFile($file_id)
	{
		return StorageFile::findOne(['id' => $file_id, 'is_deleted' => 0]);
	}

	/**
	 * @param $imageId
	 * @return StorageFile|null
	 */
	public function getImage($imageId)
	{
		return StorageFile::find()->where(['id' => $imageId, 'mime_type' => $this->imageMimeTypes, 'is_deleted' => 0])->one();
	}

	public function getPresignedUrl($file_id, $time = 1800)
	{
		$file = StorageFile::findOne(['id' => $file_id, 'is_deleted' => 0]);

		if (!$file) {
			throw new Exception("File not found.");
		}

		if (!$this->downloadValidationKey) {
			throw new InvalidConfigException("downloadValidationKey does not set.");
		}

		$data = $file->getAttributes([
			'id', 'name_new_compound', 'hash_file', 'hash_name'
		]);

		$data['time'] = $time;

		$data['created_at'] = time();

		$data = Json::encode($data);

		$token = base64_encode(Yii::$app->security->encryptByKey($data, $this->downloadValidationKey));

		return Url::to([$this->downloadUrl, 'filename' => $file->name_original, 'token' => $token]);
	}
}
