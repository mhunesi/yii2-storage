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
                'mime_type' => $this->string(),
                'extension' => $this->string(),
                'hash_file' => $this->string(),
                'hash_name' => $this->string(),
                'file_size' => $this->integer()->defaultValue('0'),
                'caption' => $this->string(),
                'inline_disposition' => $this->boolean()->defaultValue('0'),
                'parent_id' => $this->boolean()->defaultValue(null),
                'filter_identifier' => $this->string()->defaultValue(null),
                'resolution_width' => $this->integer()->defaultValue(null),
                'resolution_height' => $this->integer()->defaultValue(null),
                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'is_deleted' => $this->integer()->defaultValue(0),
            ]
        );

        $this->createIndex('index_id_hash_name_is_deleted', '{{%storage_file}}', ['id', 'hash_name', 'is_deleted']);
        $this->createIndex('index_name_new_compound', '{{%storage_file}}', ['name_new_compound']);
        $this->createIndex('storage_file_index1', '{{%storage_file}}', ['folder_id', 'is_hidden', 'is_deleted', 'name_original']);
        $this->createIndex('storage_file_index2', '{{%storage_file}}', ['is_deleted', 'id']);
        $this->createIndex('index_created_user_id', '{{%storage_file}}', ['created_by']);
        $this->createIndex('index_updated_user_id', '{{%storage_file}}', ['updated_by']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%storage_file}}');
    }
}
