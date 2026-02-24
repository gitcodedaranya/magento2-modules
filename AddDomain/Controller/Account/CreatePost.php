<?php

namespace Ime\AddDomain\Controller\Account;

use Magento\Customer\Controller\Account\CreatePost as CoreCreatePost;

class CreatePost extends CoreCreatePost
{
    
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $response = $objectManager->get(\Magento\Framework\App\ResponseInterface::class);
        $messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        $domainChecker =$objectManager->get('\Ime\AddDomain\Model\DomainChecker');
        // Your custom logic here (before register)
        $postData = $this->getRequest()->getPostValue();
       // print_r($postData);
        if(isset($postData['email'])){
            $domain = substr(strrchr($postData['email'], "@"), 1);
        } else {
           $domain=''; 
        }

        if (!$domainChecker->isDomainExists($domain)) {
            $messageManager->addErrorMessage(__('Your email address is not in our company database. Please try again with your official company email.')); // Validation failed
            $response->setRedirect('/customer/account/create'); // Stop flow and redirect back to register page
            return false;  // Stops original flow
        } else { //If you want to Redirect to custom intermediate page then use it otherwise remove this part
           // $this->messageManager->addSuccessMessage(__('Thank you for registering. We’ll verify your details and notify you by email once your dashboard is ready for login.'));
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $storeId = $storeManager->getStore()->getId();

                $websiteId = $storeManager->getStore($storeId)->getWebsiteId();

                try {
                    $customer = $objectManager->get('\Magento\Customer\Api\Data\CustomerInterfaceFactory')->create();
                    $customer->setWebsiteId($websiteId);
                    $email = $postData['email'];
                    $customer->setEmail($email);
                    $customer->setFirstname($postData['firstname']);
                    $customer->setLastname($postData['lastname']);
                    $hashedPassword = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface')->getHash($postData['password'], true);

                    $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface')->save($customer, $hashedPassword);

                    $customer = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
                    $customer->setWebsiteId($websiteId)->loadByEmail($email);
                    //$response->setRedirect('thankyou');

                    $resultRedirect = $objectManager->get(\Magento\Framework\Controller\Result\RedirectFactory::class)->create();
                    $resultRedirect->setPath('thankyou'); // Using page_id for CMS page
                     return $resultRedirect; // Return the redirect result
                   
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            
           
            
        }
        

        // Continue to original controller flow
       //return parent::execute();
    }

   
}
