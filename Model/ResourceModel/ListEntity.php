<?php
namespace TR\ShoppingList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ListEntity extends AbstractDb
{
    protected function _construct()
    {
        // table name, primary key
        $this->_init('tr_shopping_list', 'list_id');
    }
}