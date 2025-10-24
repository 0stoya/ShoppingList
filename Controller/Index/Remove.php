<?php
namespace TR\ShoppingList\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use TR\ShoppingList\Model\ItemFactory;
use TR\ShoppingList\Model\ShoppingListFactory;
use Magento\Customer\Model\Session as CustomerSession;

class Remove extends Action
{
    protected $itemFactory;
    protected $shoppingListFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        ShoppingListFactory $shoppingListFactory,
        CustomerSession $customerSession
    ) {
        $this->itemFactory = $itemFactory;
        $this->shoppingListFactory = $shoppingListFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        $itemId = (int) $this->getRequest()->getParam('item_id');
        $item = $this->itemFactory->create()->load($itemId);

        if (!$item->getId()) {
            $this->messageManager->addErrorMessage(__('Item not found.'));
            return $this->_redirect('*/*/index');
        }

        $list = $this->shoppingListFactory->create()->load($item->getListId());
        $customerId = $this->customerSession->getCustomerId();

        if (!$list->getId() || $list->getCustomerId() != $customerId) {
            $this->messageManager->addErrorMessage(__('Not authorized.'));
            return $this->_redirect('*/*/index');
        }

        $item->delete();
        $this->messageManager->addSuccessMessage(__('Item removed.'));
        return $this->_redirect('*/*/view', ['list_id' => $list->getId()]);
    }
}
