<?php
namespace 0stoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

class ShoppingList extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\0stoya\ShoppingList\Model\ResourceModel\ShoppingList::class);
    }
}