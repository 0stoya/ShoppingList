<?php
namespace TR\ShoppingList\Controller\List;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingList as ShoppingListResource;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends Action implements HttpPostActionInterface
{
    protected $customerSession;
    protected $shoppingListFactory;
    protected $shoppingListResource;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShoppingListFactory $shoppingListFactory,
        ShoppingListResource $shoppingListResource,
        JsonFactory $jsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->shoppingListFactory = $shoppingListFactory;
        $this->shoppingListResource = $shoppingListResource;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $response = ['success' => false];

        if (!$this->getRequest()->isPost() || !$this->customerSession->authenticate(false)) {
            $response['message'] = __('Invalid request.');
            return $resultJson->setData($response);
        }

        $listName = trim($this->getRequest()->getParam('list_name'));

        if (empty($listName)) {
            $response['message'] = __('List name cannot be empty.');
            return $resultJson->setData($response);
        }

        try {
            $list = $this->shoppingListFactory->create();
            $list->setListName($listName)
                 ->setCustomerId($this->customerSession->getCustomerId());
            
            $this->shoppingListResource->save($list);
            $response['success'] = true;
            $response['message'] = __('You created the "%1" shopping list.', $listName);
            $response['list_id'] = $list->getId();

        } catch (\Exception $e) {
            $response['message'] = __('We can\'t save the shopping list right now.');
        }

        return $resultJson->setData($response);
    }
}