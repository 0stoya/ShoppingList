<?php
namespace TR\ShoppingList\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use TR\ShoppingList\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

class RemoveProductObserver implements ObserverInterface
{
    protected $itemCollectionFactory;

    public function __construct(
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('product_id', $product->getId());
        foreach ($collection as $item) {
            $item->delete();
        }
    }
}
