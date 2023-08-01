<?php

namespace mhunesi\storage\models;

use mhunesi\storage\Storage;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use mhunesi\storage\helpers\FileHelper;
use mhunesi\storage\models\StorageImage;

/**
 * This is the model class for table "storage_file".
 *
 * @property int $id
 * @property string $storage_key
 * @property int|null $is_hidden
 * @property int|null $is_visibility
 * @property int|null $folder_id
 * @property string|null $name_original
 * @property string|null $name_new
 * @property string|null $name_new_compound
 * @property string|null $mime_type
 * @property string|null $extension
 * @property string|null $hash_file
 * @property string|null $hash_name
 * @property int|null $file_size
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $is_deleted
 * @property int|null $parent_id
 * @property string|null $filter_identifier
 * @property int|null $resolution_height
 * @property int|null $resolution_width
 * @property string|null $caption
 * @property int|null $inline_disposition
 * @property string|null $path_prefix
 * @property string|null $file_path
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property StorageFile[] $children
 * @property boolean $isImage
 * @property Storage $storage
 * @property boolean $fileExists
 * @property boolean $sizeReadable
 * @property string $path
 * @property string $fullPath
 * @property string $url
 */
class StorageFile extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::class,
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => 'updated_at',
			],
			[
				'class' => BlameableBehavior::class,
			]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'storage_file';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['created_at', 'updated_at', 'created_by', 'updated_by', 'resolution_height', 'resolution_width', 'inline_disposition', 'parent_id'], 'integer'],
			[['name_original', 'name_new', 'name_new_compound', 'mime_type', 'extension', 'hash_file', 'hash_name', 'path_prefix', 'caption'], 'string', 'max' => 255],
			[['filter_identifier'], 'string', 'max' => 40],
			[['file_path'], 'string', 'max' => 1000],
			[['storage_key'], 'string', 'max' => 20],
			[['name_original', 'name_new', 'mime_type', 'name_new_compound', 'extension', 'hash_file', 'hash_name', 'storage_key'], 'required'],
			[['folder_id', 'file_size', 'is_deleted'], 'safe'],
			[['is_hidden', 'is_deleted', 'is_visibility'], 'boolean'],
			[['path_prefix', 'file_path', 'folder_id'], 'trim'],
			[['path_prefix', 'file_path', 'folder_id'], 'default', 'value' => null],
			[['is_hidden', 'is_deleted', 'is_visibility'], 'default', 'value' => 0],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('storage_file', 'ID'),
			'storage_key' => Yii::t('storage_file', 'ID'),
			'is_hidden' => Yii::t('storage_file', 'Is Hidden'),
			'is_visibility' => Yii::t('storage_file', 'Is Visibility'),
			'folder_id' => Yii::t('storage_file', 'Folder ID'),
			'name_original' => Yii::t('storage_file', 'Name Original'),
			'name_new' => Yii::t('storage_file', 'Name New'),
			'name_new_compound' => Yii::t('storage_file', 'Name New Compound'),
			'mime_type' => Yii::t('storage_file', 'Mime Type'),
			'extension' => Yii::t('storage_file', 'Extension'),
			'hash_file' => Yii::t('storage_file', 'Hash File'),
			'hash_name' => Yii::t('storage_file', 'Hash Name'),
			'file_size' => Yii::t('storage_file', 'File Size'),
			'is_deleted' => Yii::t('storage_file', 'Is Deleted'),
			'parent_id' => Yii::t('storage_file', 'Parent ID'),
			'filter_identifier' => Yii::t('storage_file', 'Filter Identifier'),
			'resolution_width' => Yii::t('storage_file', 'Resolution Width'),
			'resolution_height' => Yii::t('storage_file', 'Resolution Height'),
			'caption' => Yii::t('storage_file', 'Caption'),
			'path_prefix' => Yii::t('storage_file', 'Path Prefix'),
			'file_path' => Yii::t('storage_file', 'File Path'),
			'inline_disposition' => Yii::t('storage_file', 'Inline Disposition'),
			'created_at' => Yii::t('storage_file', 'Created At'),
			'updated_at' => Yii::t('storage_file', 'Updated At'),
			'created_by' => Yii::t('storage_file', 'Created By'),
			'updated_by' => Yii::t('storage_file', 'Updated By'),
		];
	}

	/**
	 * Delete a given file.
	 *
	 * Override default implementation. Mark as deleted and remove files from file system.
	 *
	 * Keep file in order to provide all file references.
	 *
	 * @return boolean
	 */
	public function delete()
	{
		if ($this->beforeDelete()) {

			if (!$this->storage->fileSystem->delete($this->path)) {
				Yii::error("Unable to remove file from filesystem: " . $this->path);
			} else {
				if (!$this->parent_id) {
					foreach ($this->children as $child) {
						$child->delete();
					}
				}

				$this->updateAttributes(['is_deleted' => true]);
			}

			$this->afterDelete();
			return true;
		}

		return false;
	}

	public function getChildren()
	{
		return $this->hasMany(self::class, ['parent_id' => 'id']);
	}

	public function getStorage()
	{
		return Yii::$app->storage->getStorage($this->storage_key);
	}

	public function getIsImage()
	{
		return in_array($this->mime_type, Yii::$app->storage->imageMimeTypes);
	}

	public function getSizeReadable()
	{
		return FileHelper::humanReadableFilesize($this->file_size);
	}

	public function getStream()
	{
		return $this->storage->fileSystem->readStream($this->name_new_compound);
	}

	public function getFileExists()
	{
		return $this->storage->fileSystem->has($this->name_new_compound);
	}

	public function applyFilter($filterId)
	{
		return $this->storage->applyFilter($this->id, $filterId);
	}

	public function getPath()
	{
		return is_null($this->file_path) ? $this->name_new_compound : ($this->file_path . DIRECTORY_SEPARATOR . $this->name_new_compound);
	}

	public function getFullPath()
	{
		return is_null($this->path_prefix) ? $this->getPath() : ($this->path_prefix . DIRECTORY_SEPARATOR . $this->getPath());
	}

	public function getUrl()
	{
		return $this->storage->fileSystem->getUrl($this->path);
	}
}
