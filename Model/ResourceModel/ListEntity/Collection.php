<?php
namespace 0stoya\ShoppingList\Model\ResourceModel\ListEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \0stoya\ShoppingList\Model\ListEntity::class,
            \0stoya\ShoppingList\Model\ResourceModel\ListEntity::class
        );
    }
}