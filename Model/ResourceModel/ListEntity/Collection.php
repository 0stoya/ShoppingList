<?php
namespace TR\ShoppingList\Model\ResourceModel\ListEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \TR\ShoppingList\Model\ListEntity::class,
            \TR\ShoppingList\Model\ResourceModel\ListEntity::class
        );
    }
}