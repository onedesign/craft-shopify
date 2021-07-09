<?php

namespace onedesign\craftshopify\migrations;

use craft\db\Migration;

/**
 * m210709_171421_add_webhook_id_column migration.
 */
class m210709_171421_add_webhook_id_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%shopifywebhooks}}', 'webhookId', $this->text()->after('id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210709_171421_add_webhook_id_column cannot be reverted.\n";
        return false;
    }
}
