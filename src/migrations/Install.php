<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\migrations;

use bitsoflove\translation\Translation;

use Craft;
use craft\db\Migration;
use bitsoflove\translation\Constants;
/**
 * Translation Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class Install extends Migration
{
    public $driver;

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeForeignKeys();
        $this->removeTables();

        return true;
    }

    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(Constants::TABLE_SOURCE);
        if ($tableSchema === null) {
            $tablesCreated = true;
    
            $this->createTable(Constants::TABLE_SOURCE, [
                'id' => $this->primaryKey(),
                'category' => $this->string()->defaultValue('site'),
                'message' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }
        
        $tableSchema = Craft::$app->db->schema->getTableSchema(Constants::TABLE_TRANSLATION);
        if ($tableSchema === null) {
            $tablesCreated = true;

            $this->createTable(Constants::TABLE_TRANSLATION, [
                'id' => $this->integer()->notNull(),
                'language' => $this->string(),
                'translation' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        $this->addPrimaryKey(null, Constants::TABLE_TRANSLATION, ['id', 'language']);

        return $tablesCreated;
    }

    protected function createIndexes()
    {
        $this->createIndex(
            null,
            Constants::TABLE_SOURCE,
            'category',
            false
        );
        $this->createIndex(
            null,
            Constants::TABLE_TRANSLATION,
            ['id', 'language'],
            true
        );
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            'fk_message_source_message',
            Constants::TABLE_TRANSLATION,
            ['id'],
            Constants::TABLE_SOURCE,
            ['id'],
            'CASCADE',
            'RESTRICT'
        );
    }

    protected function removeForeignKeys()
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema(Constants::TABLE_TRANSLATION);
        if ($tableSchema !== null) {
            $this->dropForeignKey('fk_message_source_message', Constants::TABLE_TRANSLATION);
        }
    }

    protected function removeTables()
    {
        $this->dropTableIfExists(Constants::TABLE_SOURCE);
        $this->dropTableIfExists(Constants::TABLE_TRANSLATION);
    }
}
