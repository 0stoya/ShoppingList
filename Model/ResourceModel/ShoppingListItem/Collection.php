<?php
namespace Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Shopping List Item Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ostoya\ShoppingList\Model\ShoppingListItem::class,
            \Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem::class
        );
    }
}