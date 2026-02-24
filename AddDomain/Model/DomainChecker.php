<?php

namespace Ime\AddDomain\Model;

use Magento\Framework\App\ResourceConnection;

class DomainChecker
{
    protected $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Check if domain exists in custom table `ime_customer_domain`
     *
     * @param string $domain
     * @return bool
     */
    public function isDomainExists($domain)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('ime_customer_domain');  // custom table name

        $select = $connection->select()
            ->from($tableName, ['domain_name'])  // select field
            ->where('domain_name = ?', $domain);

        $result = $connection->fetchOne($select);

        return !empty($result);  // returns true if domain exists
    }
}
