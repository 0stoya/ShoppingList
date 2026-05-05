<?php
namespace Ostoya\ShoppingList\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Ostoya\ShoppingList\Model\Service\ShoppingListService;

class Create extends Action
{
    public function __construct(
        Context $context,
        private readonly CustomerSession $customerSession,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly ShoppingListService $shoppingListService
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key.'));
            return $this->_redirect('*/*/index');
        }

        $name = (string) $this->getRequest()->getParam('name');

        try {
            $this->shoppingListService->createList((int) $this->customerSession->getCustomerId(), $name);
            $this->messageManager->addSuccessMessage(__('Shopping list created.'));
        } catch (AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(__('You already have a shopping list with this name.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t create the shopping list right now.'));
        }

        return $this->_redirect('*/*/index');
    }
}
