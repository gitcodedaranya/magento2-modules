<?php
namespace Ime\AddDomain\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerLoginValidation
{
    protected $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function aroundAuthenticate(
        \Magento\Customer\Api\AccountManagementInterface $subject,
        callable $proceed,
        $username,
        $password
    ) {
        $customer = $this->customerRepository->get($username);

        $attr = $customer->getCustomAttribute('is_approve_customer');

        // If attribute doesn't exist OR not approved → block login
        if (!$attr || (int)$attr->getValue() === 0) {
            throw new LocalizedException(
                __('Your account is not approved. Please wait for admin approval.')
            );
        }

        return $proceed($username, $password);
    }
}
