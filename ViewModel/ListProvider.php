<?php
namespace TR\ShoppingList\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;
use Magento\Framework\Registry; // <-- Add this

class ListProvider implements ArgumentInterface
{
    private $customerSession;
    private $listCollectionFactory;
    private $registry; // <-- Add this

    public function __construct(
        CustomerSession $customerSession,
        ListCollectionFactory $listCollectionFactory,
        Registry $registry // <-- Inject this
    ) {
        $this->customerSession = $customerSession;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->registry = $registry; // <-- Assign this
    }

    /**
     * @return \TR\ShoppingList\Model\ResourceModel\ShoppingList\Collection|array
     */
    public function getCustomerLists()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }
        $collection = $this->listCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
        return $collection;
    }
    
    /**
     * ✅ NEW METHOD
     * Get the current product from Magento's registry.
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}