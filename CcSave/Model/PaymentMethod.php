<?php

namespace Ime\CcSave\Model;

/**
 * Pay In Store payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ccsave';

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $additionalData = $data->getData('additional_data');

        $info = $this->getInfoInstance();

        if (isset($additionalData['cc_name'])) {
            $ccname = $additionalData['cc_name'];
            $info->setCcOwner($ccname);
        }

        if (isset($additionalData['cc_number'])) {
            $last4 = substr($additionalData['cc_number'], -4);
            $info->setCcLast4($last4);
        }

        if (isset($additionalData['cc_exp_month'])) {
            $info->setCcExpMonth($additionalData['cc_exp_month']);
        }

        if (isset($additionalData['cc_exp_year'])) {
            $info->setCcExpYear($additionalData['cc_exp_year']);
        }

        if (isset($additionalData['cc_type'])) {
            $info->setCcType($additionalData['cc_type']);
        }

        return $this;
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize( \Magento\Payment\Model\InfoInterface $payment, $amount ) 
    {
        return $this;
    }
}