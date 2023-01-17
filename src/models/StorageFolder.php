<?php

namespace mhunesi\storage\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "storage_folder".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $parent_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $is_deleted
 */
class StorageFolder extends \yii\db\ActiveRecord
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
        return 'storage_folder';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'is_deleted'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],
            [['parent_id'], 'default','value' => null],
            [['is_deleted'], 'default','value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('storage_folder', 'ID'),
            'name' => Yii::t('storage_folder', 'Name'),
            'parent_id' => Yii::t('storage_folder', 'Parent ID'),
            'created_by' => Yii::t('storage_folder', 'Created By'),
            'updated_by' => Yii::t('storage_folder', 'Updated By'),
            'created_at' => Yii::t('storage_folder', 'Created At'),
            'updated_at' => Yii::t('storage_folder', 'Updated At'),
            'is_deleted' => Yii::t('storage_folder', 'Is Deleted'),
        ];
    }
}
