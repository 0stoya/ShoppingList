<?php
namespace TR\ShoppingList\Block;

use Magento\Framework\View\Element\Template;
use TR\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ShoppingListCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class ShoppingList extends Template
{
    protected $collectionFactory;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        ShoppingListCollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getCustomerLists()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }
        return $this->collectionFactory->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
    }
}
