<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Controller\List;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Ostoya\ShoppingList\Model\ShoppingListFactory;

class View extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private CustomerSession $customerSession,
        private Registry $registry,
        private ShoppingListFactory $shoppingListFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->authenticate()) {
            $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            return null;
        }

        $listId = (int) $this->getRequest()->getParam('list_id');
        if (!$listId) {
            $this->messageManager->addErrorMessage(__('No shopping list ID was specified.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $list = $this->shoppingListFactory->create()->load($listId);
        if (!$list->getId() || (int) $list->getCustomerId() !== (int) $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('The requested shopping list was not found.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $this->registry->register('current_shopping_list', $list);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set((string) $list->getListName());

        return $resultPage;
    }
}
