<?php

namespace Ime\AddDomain\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class SaveCustomerAttribute
{
    protected $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        $attribute = $customer->getCustomAttribute('is_approve_customer');

        if ($attribute === null) {
            return $customer;
        }

        $value = (int)$attribute->getValue();
        $customerId = (int)$customer->getId();

        // Get attribute ID dynamically
        $connection = $this->resource->getConnection();
        $attrId = $connection->fetchOne(
            "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'is_approve_customer' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'customer')"
        );

        if ($attrId) {
            // Check if row exists
            $exists = $connection->fetchOne(
                "SELECT value_id FROM customer_entity_int WHERE attribute_id = ? AND entity_id = ?",
                [$attrId, $customerId]
            );

            if ($exists) {
                // Update
                $connection->update(
                    'customer_entity_int',
                    ['value' => $value],
                    ['value_id = ?' => $exists]
                );
            } else {
                // Insert
                $connection->insert(
                    'customer_entity_int',
                    [
                        'attribute_id' => $attrId,
                        'entity_id'    => $customerId,
                        'value'        => $value
                    ]
                );
            }
        }

        return $customer;
    }
}
