<?php

use yii\db\Migration;

class m221205_120708_create_table_storage_file extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%storage_file}}',
            [
                'id' => $this->primaryKey(),
                'storage_key' => $this->string('20'),
                'is_hidden' => $this->boolean()->defaultValue('0'),
                'is_visibility' => $this->boolean()->defaultValue('0'),
                'folder_id' => $this->integer()->defaultValue(null),
                'name_original' => $this->string(),
                'name_new' => $this->string(),
                'name_new_compound' => $this->string(),
                'path_prefix' => $this->string(),
                'file_path' => $this->string(1000),
                'mime_type' => $this->string(50),
                'extension' => $this->string(10),
                'hash_file' => $this->string(),
                'hash_name' => $this->string(),
                'file_size' => $this->integer()->defaultValue(0),
                'caption' => $this->string(),
                'inline_disposition' => $this->boolean()->defaultValue(0),
                'parent_id' => $this->integer()->defaultValue(null),
                'filter_identifier' => $this->string(25)->defaultValue(null),
                'resolution_width' => $this->integer()->defaultValue(null),
                'resolution_height' => $this->integer()->defaultValue(null),
                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'is_deleted' => $this->integer()->defaultValue(0),
            ]
        );

        $this->createIndex('index_id_hash_name_mime_type_is_deleted', '{{%storage_file}}', ['id', 'hash_name', 'mime_type', 'is_deleted']);
        $this->createIndex('storage_file_index1', '{{%storage_file}}', ['folder_id', 'is_hidden', 'is_deleted', 'name_original']);
	$this->createIndex('storage_file_index2', '{{%storage_file}}', ['is_deleted', 'id']);
	$this->createIndex('storage_file_index3', '{{%storage_file}}', ['filter_identifier','parent_id','is_deleted']);
	$this->createIndex('storage_file_index4', '{{%storage_file}}', ['parent_id']);
	$this->createIndex('index_created_user_id', '{{%storage_file}}', ['created_by']);
        $this->createIndex('index_updated_user_id', '{{%storage_file}}', ['updated_by']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%storage_file}}');
    }
}
