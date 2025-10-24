<?php
namespace TR\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\TR\ShoppingList\Model\ResourceModel\Item::class);
    }
}
