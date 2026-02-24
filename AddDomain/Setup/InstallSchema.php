<?php  namespace Ime\AddDomain\Setup;  
use Magento\Framework\Setup\InstallSchemaInterface; 
use Magento\Framework\Setup\ModuleContextInterface;
 use Magento\Framework\Setup\SchemaSetupInterface; 
 use Magento\Setup\Exception; 
 
 class InstallSchema implements InstallSchemaInterface {   
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {  

        $installer = $setup;
        $installer->startSetup();
        try {             
            $table = $installer->getConnection()
            ->newTable( $installer->getTable('ime_customer_domains') )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 
                'nullable' => false, 
                'primary' => true], 'Id')
             ->addColumn(
                'admin_user_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'default' => 0],
                'admin user id'
                )
            ->addColumn(
                'domain_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false], 
                'domain_name')
            
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
              ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
              'Created At')
            ->setComment('Add Domain');              
            $installer->getConnection()->createTable($table);             
            $installer->endSetup();         
        } catch (Exception $err) {
             \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($err->getMessage());         
        }     
    } 
}