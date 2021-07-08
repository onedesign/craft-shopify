<?php

namespace onedesign\craftshopify\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210630_194744_add_webhook_table migration.
 */
class m210630_194744_add_webhook_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $webhookTable = Craft::$app->db->schema->getTableSchema('{{%shopifywebhooks}}');
        if ($webhookTable === null) {
            $this->createTable('{{%shopifywebhooks}}', [
                'id' => $this->primaryKey(),
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
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210630_194744_add_webhook_table cannot be reverted.\n";
        return false;
    }
}
