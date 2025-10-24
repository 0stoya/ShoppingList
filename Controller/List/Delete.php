<?php
namespace TR\ShoppingList\Controller\List;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingList as ShoppingListResource;

class Delete extends Action implements HttpGetActionInterface
{
    protected $customerSession;
    protected $shoppingListFactory;
    protected $shoppingListResource;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShoppingListFactory $shoppingListFactory,
        ShoppingListResource $shoppingListResource
    ) {
        $this->customerSession = $customerSession;
        $this->shoppingListFactory = $shoppingListFactory;
        $this->shoppingListResource = $shoppingListResource;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        if (!$this->customerSession->authenticate()) {
            return $resultRedirect;
        }

        $listId = (int) $this->getRequest()->getParam('list_id');
        $list = $this->shoppingListFactory->create()->load($listId);

        if (!$list->getId() || $list->getCustomerId() != $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('The requested shopping list was not found.'));
            return $resultRedirect;
        }

        try {
            $this->shoppingListResource->delete($list);
            $this->messageManager->addSuccessMessage(__('The shopping list has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete the shopping list right now.'));
        }
        
        return $resultRedirect;
    }
}