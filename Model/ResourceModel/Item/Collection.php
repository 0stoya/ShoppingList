<?php
namespace TR\ShoppingList\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \TR\ShoppingList\Model\Item::class,
            \TR\ShoppingList\Model\ResourceModel\Item::class
        );
    }
}
