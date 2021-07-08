<?php

namespace onedesign\craftshopify\migrations;

use craft\db\Migration;

/**
 * m210701_192743_allow_null_metafieldId migration.
 */
class m210701_192743_allow_null_metafieldId extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%shopifyproducts}}', 'bodyHtmlMetafieldId', $this->bigInteger());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210701_192743_allow_null_metafieldId cannot be reverted.\n";
        return false;
    }
}
