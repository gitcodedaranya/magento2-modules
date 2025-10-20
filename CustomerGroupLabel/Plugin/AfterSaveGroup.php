<?php
namespace Ime\CustomerGroupLabel\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;
use Psr\Log\LoggerInterface;

class AfterSaveGroup
{
    protected $request;
    protected $uploaderFactory;
    protected $filesystem;
    protected $groupFactory;
    protected $groupRepository;
    protected $logger;

    public function __construct(
        RequestInterface $request,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        GroupFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    public function afterSave(
        GroupRepositoryInterface $subject,
        $resultGroup
    ) {
        $data = $this->request->getPostValue();
        $files = $this->request->getFiles();
       // $this->logger->info('afterSave plugin fired for customer grouprr');

        try {
           
            $groupId = $resultGroup->getId(); //$this->request->getParam('id');
            $groupModel = $this->groupFactory->create()->load($groupId);
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

            if (isset($data['group_message'])) {
                $groupModel->setData('group_message', $data['group_message']);
            }
           /* $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/group_debug.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('AfterSaveGroup Data: ' . print_r($data, true));
            $logger->info('AfterSaveGroup Files: ' . print_r($files, true)); */
            if (!empty($data['group_logo']['delete'])) {
                // delete image from filesystem
               // print_r($data);
                $mediaRoot = $mediaDirectory->getAbsolutePath();
                $existingPath = $data['group_logo']['value'] ?? '';
               $relativePath = ltrim($existingPath, '/');
                //echo $existingPath;exit;
                if ($existingPath && $mediaDirectory->isExist($relativePath)) {
                    $mediaDirectory->delete($relativePath);
                }
                $logoPath = ''; // clear from DB
                 $groupModel->setData('group_logo', $logoPath);
            }

            // Handle new upload
            elseif(!empty($files['group_logo']) && !empty($files['group_logo']['name'])) {

                $file = $files['group_logo'];
                if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                    try {
                        $uploader = $this->uploaderFactory->create(['fileId' => 'group_logo']);
                        $uploader->setAllowedExtensions(['jpg','jpeg','png','gif','webp']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);

                       
                        $target = 'customer_group_logo';
                        $result = $uploader->save($mediaDirectory->getAbsolutePath($target));

                        if (!empty($result['file'])) {
                            $logoPath = ltrim($result['file'], '/');
                            $groupModel->setData('group_logo', $logoPath);
                        }
                    } catch (\Exception $e) {
                        // handle/log error if you have logger
                    }
                }
            } elseif (!empty($data['group_logo'])) {
                $groupModel->setData('group_logo', $data['group_logo']);
            }

            $groupModel->save(); // works here (model, not data object)

        } catch (\Exception $e) {
            $this->logger->error('Error saving group logo/message: ' . $e->getMessage());
        }

        return $resultGroup;
    }
}
?>