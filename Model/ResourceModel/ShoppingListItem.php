<?php
namespace TR\ShoppingList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Shopping List Item Resource Model
 */
class ShoppingListItem extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tr_shopping_list_item', 'item_id');
    }
}