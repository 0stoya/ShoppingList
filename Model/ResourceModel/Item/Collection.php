<?php
namespace Ostoya\ShoppingList\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Ostoya\ShoppingList\Model\Item::class,
            \Ostoya\ShoppingList\Model\ResourceModel\Item::class
        );
    }
}
