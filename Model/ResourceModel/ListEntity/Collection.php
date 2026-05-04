<?php
namespace Ostoya\ShoppingList\Model\ResourceModel\ListEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Ostoya\ShoppingList\Model\ListEntity::class,
            \Ostoya\ShoppingList\Model\ResourceModel\ListEntity::class
        );
    }
}