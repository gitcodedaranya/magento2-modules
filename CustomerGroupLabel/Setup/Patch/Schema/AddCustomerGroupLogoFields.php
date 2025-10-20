<?php
namespace Ime\CustomerGroupLabel\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCustomerGroupLogoFields implements SchemaPatchInterface
{
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();

        $connection = $setup->getConnection();
        $table = $setup->getTable('customer_group');

        if (!$connection->tableColumnExists($table, 'group_logo')) {
            $connection->addColumn($table, 'group_logo', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Group Logo'
            ]);
        }

        if (!$connection->tableColumnExists($table, 'group_message')) {
            $connection->addColumn($table, 'group_message', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Group Message'
            ]);
        }

        $setup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
