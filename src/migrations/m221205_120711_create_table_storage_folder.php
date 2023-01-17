<?php

use yii\db\Migration;

class m221205_120711_create_table_storage_folder extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        $this->createTable(
            '{{%storage_folder}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'parent_id' => $this->integer(),
                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'is_deleted' => $this->integer()->defaultValue(0),
            ],
            $tableOptions
        );


        $this->createIndex('index_created_folder_user_id', '{{%storage_folder}}', ['created_by']);
        $this->createIndex('index_updated_folder_user_id', '{{%storage_folder}}', ['updated_by']);
        $this->createIndex('index_parent_id', '{{%storage_folder}}', ['parent_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%storage_folder}}');
    }
}
