<?php

namespace Ime\AddDomain\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class NotifyAdminOnRegister implements ObserverInterface
{
    protected $transportBuilder;
    protected $emailTemplate;
    protected $storeManager;
    protected $scopeConfig;
    protected $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        \Magento\Email\Model\Template $emailTemplate,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->emailTemplate = $emailTemplate;
        $this->storeManager     = $storeManager;
        $this->scopeConfig      = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {

        //$this->logger->info('ADMIN CUSTOMER OBSERVER CALLED'); //for log
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
       
        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE); // Admin email (Store → Configuration → General → Store Email Addresses)

        $adminName = $this->scopeConfig->getValue('trans_email/ident_general/name',ScopeInterface::SCOPE_STORE);
        
       // $this->logger->info('Admin Email: ' . $adminEmail.' Admin Name: '. $adminName); //for log

        $store = $this->storeManager->getStore();
        try {
            $templateCode = 'New Customer Registration to Admin';
            $template = $this->emailTemplate->load($templateCode, 'template_code');
            $transport = $this->transportBuilder
            ->setTemplateIdentifier($template->getId()) // email template id
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $store->getId(),
            ])
            ->setTemplateVars([
                'customer_name'  => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'customer_email' => $customer->getEmail(),
            ])
            ->setFrom([
                'name'  => $adminName,
                'email' =>  $adminEmail,
            ])
           
            ->addTo($adminEmail, $adminName)
            ->getTransport();
            
        //$this->logger->info('Customer Email: ' . $customer->getEmail()." templatecode: ".$template->getId());
        $transport->sendMessage();
        $this->logger->info('Customer Registration Email sent successfully from '.$customer->getId());
        } catch (\Exception $e) {
            $this->logger->critical('Customer Registration Email error: ' . $e->getMessage());
        }
    }
}
