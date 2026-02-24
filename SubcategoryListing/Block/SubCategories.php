<?php

namespace Ime\SubcategoryListing\Block;

use Magento\Framework\View\Element\Template;

class SubCategories extends Template
{
    protected $categoryFactory;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCurrentCategoryId()
    {
        $category = $this->registry->registry('current_category');
        return $category ? $category->getId() : null;
    }
    public function getCategories()
    {

        $rootCategoryId =  $this->getCurrentCategoryId();
        $category = $this->categoryFactory->create()->load($rootCategoryId);

        $categories = $category->getChildrenCategories();
        $result = [];

        foreach ($categories as $cat) {
            if ($cat->getIsActive()) {
                $result[] = $cat;
            }
        }

       

        return $result;
    }
}
