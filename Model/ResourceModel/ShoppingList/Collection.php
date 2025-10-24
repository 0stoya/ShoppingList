<?php
namespace TR\ShoppingList\Model\ResourceModel\ShoppingList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'list_id';

    protected function _construct()
    {
        $this->_init(
            \TR\ShoppingList\Model\ShoppingList::class,
            \TR\ShoppingList\Model\ResourceModel\ShoppingList::class
        );
    }

    /**
     * ✅ NEW METHOD
     * Join the item table to get the count of items for each list.
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['item_table' => $this->getTable('tr_shopping_list_item')],
            'main_table.list_id = item_table.list_id',
            ['items_count' => new \Zend_Db_Expr('COUNT(item_table.item_id)')]
        )->group('main_table.list_id');

        return $this;
    }
}