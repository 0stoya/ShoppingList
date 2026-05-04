<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Controller\Item;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use Ostoya\ShoppingList\Model\ShoppingListFactory;
use Ostoya\ShoppingList\Model\ShoppingListItemFactory;

class Delete extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private CustomerSession $customerSession,
        private ShoppingListItemFactory $itemFactory,
        private ItemResource $itemResource,
        private ShoppingListFactory $listFactory,
        private FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->authenticate() || !$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }
        $item = $this->itemFactory->create()->load((int) $this->getRequest()->getParam('item_id'));
        if (!$item->getId()) {
            $this->messageManager->addErrorMessage(__('The requested item was not found.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }
        $list = $this->listFactory->create()->load((int) $item->getListId());
        if ((int) $list->getCustomerId() !== (int) $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You do not have permission to modify this item.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('shoppinglist/list/view', ['list_id' => $item->getListId()]);
        try { $this->itemResource->delete($item); $this->messageManager->addSuccessMessage(__('The item has been removed from your shopping list.')); }
        catch (\Exception $e) { $this->messageManager->addErrorMessage(__('We can\'t remove the item right now.')); }
        return $resultRedirect;
    }
}
