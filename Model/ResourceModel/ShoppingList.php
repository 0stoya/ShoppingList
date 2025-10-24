<?php
namespace TR\ShoppingList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ShoppingList extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('tr_shopping_list', 'list_id');
    }
}