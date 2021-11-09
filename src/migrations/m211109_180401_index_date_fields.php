<?php

namespace onedesign\craftshopify\migrations;

use craft\db\Migration;

/**
 * m211109_180401_index_date_fields migration.
 */
class m211109_180401_index_date_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex(
            null,
            '{{%shopifywebhooks}}',
            'dateCreated',
            false
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex(
            $this->db->getIndexName('{{%shopifywebhooks}}', 'dateCreated', false),
            '{{%shopifywebhooks}}'
        );
    }
}
