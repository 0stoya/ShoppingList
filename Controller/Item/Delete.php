<?php
namespace TR\ShoppingList\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use TR\ShoppingList\Model\ShoppingListFactory;

class Delete extends Action implements HttpGetActionInterface
{
    protected $customerSession;
    protected $itemFactory;
    protected $itemResource;
    protected $listFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShoppingListItemFactory $itemFactory,
        ItemResource $itemResource,
        ShoppingListFactory $listFactory
    ) {
        $this->customerSession = $customerSession;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->listFactory = $listFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->authenticate()) {
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $itemId = (int) $this->getRequest()->getParam('item_id');
        $item = $this->itemFactory->create()->load($itemId);
        
        if (!$item->getId()) {
            $this->messageManager->addErrorMessage(__('The requested item was not found.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $list = $this->listFactory->create()->load($item->getListId());
        if ($list->getCustomerId() != $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You do not have permission to modify this item.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }
        
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('shoppinglist/list/view', ['list_id' => $item->getListId()]);

        try {
            $this->itemResource->delete($item);
            $this->messageManager->addSuccessMessage(__('The item has been removed from your shopping list.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t remove the item right now.'));
        }

        return $resultRedirect;
    }
}