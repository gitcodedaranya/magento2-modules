<?php
namespace Ime\CustomerGroupLabel\Plugin;

use Magento\Customer\Block\Adminhtml\Group\Edit\Form;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\RequestInterface;

class AddFieldsToCustomerGroupForm
{
    protected $resource;
    protected $request;

    public function __construct(
        ResourceConnection $resource,
        RequestInterface $request
    ) {
        $this->resource = $resource;
        $this->request = $request;
    }

    public function beforeSetForm(Form $subject, DataForm $form)
    {
        
        $form->setData('enctype', 'multipart/form-data'); // Set enctype for file uploads
        $fieldset = $form->getElement('base_fieldset');
        if (!$fieldset) {
            return [$form];
        }

        // Get customer group ID from URL
        $groupId = (int) $this->request->getParam('id');
        $logoValue = '';
        $messageValue = '';

        if ($groupId) {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('customer_group');
            $select = $connection->select()
                ->from($table, ['group_logo', 'group_message'])
                ->where('customer_group_id = ?', $groupId);
            $data = $connection->fetchRow($select);

            if ($data) {
                $logoValue = 'customer_group_logo/'.$data['group_logo'];
                $messageValue = $data['group_message'];
            }
        }

        // Add Group Logo field
        if (!$fieldset->getElement('group_logo')) {
            $fieldset->addField(
                'group_logo',
                'image',
                [
                    'name'  => 'group_logo',
                    'label' => __('Group Logo'),
                    'title' => __('Group Logo'),
                    'note'  => __('Upload a logo for this customer group'),
                    'value' => $logoValue,
                ]
            );
        }

        // Add Welcome Message field
        if (!$fieldset->getElement('group_message')) {
            $fieldset->addField(
                'group_message',
                'textarea',
                [
                    'name'  => 'group_message',
                    'label' => __('Welcome Message'),
                    'title' => __('Welcome Message'),
                    'required' => false,
                    'value' => $messageValue,
                ]
            );
        }

        return [$form];
    }
}
