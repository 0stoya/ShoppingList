<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Controller\List;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList as ShoppingListResource;
use Ostoya\ShoppingList\Model\ShoppingListFactory;

class Delete extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private CustomerSession $customerSession,
        private ShoppingListFactory $shoppingListFactory,
        private ShoppingListResource $shoppingListResource,
        private FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        if (!$this->customerSession->authenticate() || !$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }
        $list = $this->shoppingListFactory->create()->load((int) $this->getRequest()->getParam('list_id'));
        if (!$list->getId() || (int) $list->getCustomerId() !== (int) $this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('The requested shopping list was not found.'));
            return $resultRedirect;
        }
        try { $this->shoppingListResource->delete($list); $this->messageManager->addSuccessMessage(__('The shopping list has been deleted.')); }
        catch (\Exception $e) { $this->messageManager->addErrorMessage(__('We can\'t delete the shopping list right now.')); }
        return $resultRedirect;
    }
}
