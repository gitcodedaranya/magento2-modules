<?php
namespace Ime\AddDomain\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;

class SaveDomain extends Action
{
    protected $resultPageFactory;
    protected $authSession;
    public function __construct(Action\Context $context, PageFactory $resultPageFactory, AuthSession $authSession)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->authSession = $authSession;
    }

    public function execute()
    {
        if ($this->authSession->getUser() !== null) {
            $admin_user_id = $this->authSession->getUser()->getId();
        }

        $created_at = date('Y-m-d H:i:s');
       // echo "<pre>";print_r($_REQUEST);
        //echo "admin_user_id: ".$admin_user_id;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$conn = $resource->getConnection();
		$ime_customer_domain = $resource->getTableName('ime_customer_domain');
        if(isset($_REQUEST['domain'])){
            $seldetails = "Select * FROM ".$ime_customer_domain." Where admin_user_id=".$admin_user_id;
            $Rows = $conn->fetchAll($seldetails);
            if(count($Rows)>0){
                //first delete data
                $Delsql = "DELETE FROM ".$ime_customer_domain." WHERE admin_user_id=".$admin_user_id;
                $conn->query($Delsql);

            }

            foreach($_REQUEST['domain'] as $domains){
                $_sql = "INSERT INTO " . $ime_customer_domain . " (id, admin_user_id, domain_name, created_at) VALUES ('', '".$admin_user_id."', '".$domains."', '".$created_at."')";
		        $conn->query($_sql);
            }
        }
        $messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultPage = $this->resultPageFactory->create();
		$resultRedirect->setRefererUrl('*/*/');
		$messageManager->addSuccess( __('Domains have been successfully saved') );
		return $resultRedirect; 
    }

}