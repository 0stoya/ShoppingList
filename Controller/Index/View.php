<?php
namespace TR\ShoppingList\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use TR\ShoppingList\Model\ShoppingListFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    protected $shoppingListFactory;
    protected $customerSession;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        ShoppingListFactory $shoppingListFactory,
        CustomerSession $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->shoppingListFactory = $shoppingListFactory;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        $listId = (int) $this->getRequest()->getParam('list_id');
        $customerId = $this->customerSession->getCustomerId();

        $list = $this->shoppingListFactory->create()->load($listId);
        if (!$list->getId() || $list->getCustomerId() != $customerId) {
            $this->messageManager->addErrorMessage(__('Shopping list not found.'));
            return $this->_redirect('*/*/index');
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('shoppinglist_view')->setData('list', $list);
        $this->_view->renderLayout();
    }
}
