<?php

namespace Ime\CcSave\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;

class CcSaveConfigProvider implements ConfigProviderInterface
{
    /**
    * @param CcConfig $ccConfig
    * @param Source $assetSource
    */
    public function __construct(
        public \Magento\Payment\Model\CcConfig $ccConfig,
        public Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
    * @var string[]
    */
    protected $_methodCode = 'ccsave';

    /**
    * {@inheritdoc}
    */
    public function getConfig()
    {
        return [
            'payment' => [
                'ccsave' => [
                    'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => $this->ccConfig->hasVerification(),
                ]
            ]
        ];
    }
}