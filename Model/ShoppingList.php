<?php
namespace Ostoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

class ShoppingList extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Ostoya\ShoppingList\Model\ResourceModel\ShoppingList::class);
    }
}