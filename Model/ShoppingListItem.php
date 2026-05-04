<?php
namespace Ostoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Shopping List Item Model
 *
 * @method int getListId()
 * @method \Ostoya\ShoppingList\Model\ShoppingListItem setListId(int $listId)
 * @method int getProductId()
 * @method \Ostoya\ShoppingList\Model\ShoppingListItem setProductId(int $productId)
 * @method float getQty()
 * @method \Ostoya\ShoppingList\Model\ShoppingListItem setQty(float $qty)
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
        $this->_init(\Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem::class);
    }
}