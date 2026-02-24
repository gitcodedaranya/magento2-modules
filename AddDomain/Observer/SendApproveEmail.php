<?php
namespace Ime\AddDomain\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Psr\Log\LoggerInterface;

class SendApproveEmail implements ObserverInterface
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
        $this->storeManager = $storeManager;
        $this->scopeConfig      = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE); // Admin email (Store → Configuration → General → Store Email Addresses)
        $adminName = $this->scopeConfig->getValue('trans_email/ident_general/name',ScopeInterface::SCOPE_STORE);

        try {
          

            $oldValue = $customer->getOrigData('is_approve_customer');  // PREVIOUS VALUE

            $newValue = $customer->getData('is_approve_customer'); //  CURRENT VALUE
            $attr = $customer->getCustomAttribute('is_approve_customer');
            $this->logger->info('Welcome email CUSTOMR OBSERVER CALLED: '. $newValue); //for log
            if ($oldValue == 0 && $newValue == 1) {

                $storeId = $this->storeManager->getStore()->getId();
                $templateCode = 'Approve mail to Customer';
                $template = $this->emailTemplate->load($templateCode, 'template_code');
                // ADMIN EMAIL TEMPLATE CODE
                $this->logger->info('TemplateId: '. $template->getId()); //for log
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($template->getId())
                    ->setTemplateOptions([
                        'area'  => 'frontend',
                        'store' => $storeId
                    ])
                    ->setTemplateVars([
                    'customer_name'  => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'customer_email' => $customer->getEmail(),
                    ])
                    ->setFrom([
                        'name'  => $adminName,
                        'email' =>  $adminEmail,
                    ])

                    ->addTo($customer->getEmail())
                    ->getTransport();

                $transport->sendMessage();
                 $this->logger->info('Customer Approve mail sent successfully to '.$customer->getEmail());
            }

        } catch (\Exception $e) {
            $this->logger->error('Approve email error: ' . $e->getMessage());
        }
    }
}
