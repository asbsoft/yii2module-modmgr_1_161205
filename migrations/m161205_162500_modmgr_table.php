<?php

use asb\yii2\modules\modmgr_1_161205\Module;

use asb\yii2\modules\modmgr_1_161205\models\Modmgr as Model;

use yii\db\Schema;
use yii\db\Migration;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class m161205_162500_modmgr_table extends Migration
{
    protected $tableName;
    protected $idxNamePrefix;

    public function init()
    {
        parent::init();

        $this->tableName = Model::tableName();

        //$this->idxNamePrefix = 'idx-' . Model::TABLE_NAME; // deprecated constant
        $this->idxNamePrefix = 'idx-' . Model::baseTableName();
    }

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->tableName, [
            'id'             => $this->primaryKey(),
            'module_id'      => $this->string(255)->notNull(),
            'parent_uid'     => $this->string(255)->notNull(),
            'name'           => $this->string(255)->notNull(),
            'is_active'      => $this->boolean()->notNull()->defaultValue(false),
            'module_class'   => $this->string(255)->notNull(),
            'bootstrap'      => $this->string(255),
            'config_default' => $this->text()->notNull()->defaultValue(''),
            'config_add'     => $this->text()->notNull()->defaultValue(''),
            'create_at'      => $this->datetime()->notNull(),
            'update_at'      => $this->timestamp(),
        ],$tableOptions);
    }

    public function safeDown()
    {
        //echo basename(__FILE__, '.php') . " cannot be reverted.\n";
        //return false;
        $this->dropTable($this->tableName);
    }

}
