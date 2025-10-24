<?php
namespace TR\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Shopping List Item Model
 *
 * @method int getListId()
 * @method \TR\ShoppingList\Model\ShoppingListItem setListId(int $listId)
 * @method int getProductId()
 * @method \TR\ShoppingList\Model\ShoppingListItem setProductId(int $productId)
 * @method float getQty()
 * @method \TR\ShoppingList\Model\ShoppingListItem setQty(float $qty)
 */
class ShoppingListItem extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\TR\ShoppingList\Model\ResourceModel\ShoppingListItem::class);
    }
}