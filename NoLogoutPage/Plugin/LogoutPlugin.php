<?php
namespace Ime\NoLogoutPage\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory;

class LogoutPlugin
{
    protected $resultRedirectFactory;

    public function __construct(RedirectFactory $resultRedirectFactory)
    {
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(
        \Magento\Customer\Controller\Account\Logout $subject,
        \Closure $proceed
    ) {
        // Perform the actual logout
        $proceed();

        // Then redirect directly to homepage (change path if needed)
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('/'); // You can change '/' to 'customer/account/login' etc.

        return $resultRedirect;
    }
}
