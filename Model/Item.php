<?php
namespace 0stoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\0stoya\ShoppingList\Model\ResourceModel\Item::class);
    }
}
