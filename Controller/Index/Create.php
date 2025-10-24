<?php
namespace TR\ShoppingList\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use TR\ShoppingList\Model\ShoppingListFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Create extends Action
{
    protected $shoppingListFactory;
    protected $customerSession;
    protected $formKeyValidator;

    public function __construct(
        Context $context,
        ShoppingListFactory $shoppingListFactory,
        CustomerSession $customerSession,
        FormKeyValidator $formKeyValidator
    ) {
        $this->shoppingListFactory = $shoppingListFactory;
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
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

        $name = trim($this->getRequest()->getParam('name'));
        if (!$name) {
            $this->messageManager->addErrorMessage(__('List name is required.'));
            return $this->_redirect('*/*/index');
        }

        $list = $this->shoppingListFactory->create();
        $list->setData([
            'customer_id' => $this->customerSession->getCustomerId(),
            'name' => $name
        ]);
        $list->save();

        $this->messageManager->addSuccessMessage(__('Shopping list created.'));
        return $this->_redirect('*/*/index');
    }
}
