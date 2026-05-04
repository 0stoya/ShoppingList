<?php
namespace Ostoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Ostoya\ShoppingList\Model\ResourceModel\Item::class);
    }
}
