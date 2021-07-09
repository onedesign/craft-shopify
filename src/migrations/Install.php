<?php
/**
 * Craft Shopify plugin for Craft CMS 3.x
 *
 * Bring Shopify products into Craft
 *
 * @link      https://onedesigncompany.com/
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    One Design Company
 * @package   CraftShopify
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $productTable = Craft::$app->db->schema->getTableSchema('{{%shopifyproducts}}');
        if ($productTable === null) {
            $this->createTable('{{%shopifyproducts}}', [
                'id' => $this->integer()->notNull(),
                'bodyHtml' => $this->mediumText()->notNull(),
                'bodyHtmlMetafieldId' => $this->bigInteger(),
                'shopifyId' => $this->bigInteger(13)->notNull(),
                'jsonData' => $this->mediumText()->notNull(),
                'productType' => $this->text()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);
        }

        $webhookTable = Craft::$app->db->schema->getTableSchema('{{%shopifywebhooks}}');
        if ($webhookTable === null) {
            $this->createTable('{{%shopifywebhooks}}', [
                'id' => $this->primaryKey(),
                'webhookId' => $this->text()->notNull(),
                'payload' => $this->text(),
                'errors' => $this->text(),
                'type' => $this->string(255),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return true;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(),
            '{{%shopifyproducts}}',
            'shopifyId',
            true
        );

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        // give it a foreign key to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(),
            '{{%shopifyproducts}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%shopifyproducts}}');
        $this->dropTableIfExists('{{%shopifywebhooks}}');
    }
}
