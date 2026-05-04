<?php
namespace 0stoya\ShoppingList\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Shopping List Item Model
 *
 * @method int getListId()
 * @method \0stoya\ShoppingList\Model\ShoppingListItem setListId(int $listId)
 * @method int getProductId()
 * @method \0stoya\ShoppingList\Model\ShoppingListItem setProductId(int $productId)
 * @method float getQty()
 * @method \0stoya\ShoppingList\Model\ShoppingListItem setQty(float $qty)
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
        $this->_init(\0stoya\ShoppingList\Model\ResourceModel\ShoppingListItem::class);
    }
}