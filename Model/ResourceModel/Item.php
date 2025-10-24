<?php
namespace TR\ShoppingList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('tr_shoppinglist_item', 'item_id');
    }
}
