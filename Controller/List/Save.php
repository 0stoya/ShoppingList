<?php
namespace Ostoya\ShoppingList\Controller\List;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Ostoya\ShoppingList\Model\Service\ShoppingListService;

class Save extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly CustomerSession $customerSession,
        private readonly ShoppingListService $shoppingListService,
        private readonly JsonFactory $jsonFactory
    ) {
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

        $listName = (string) $this->getRequest()->getParam('list_name');

        try {
            $list = $this->shoppingListService->createList((int) $this->customerSession->getCustomerId(), $listName);
            $response['success'] = true;
            $response['message'] = __('You created the "%1" shopping list.', $list->getData('list_name'));
            $response['list_id'] = (int) $list->getId();
        } catch (AlreadyExistsException $e) {
            $response['message'] = __('You already have a shopping list with this name. Please choose a different name.');
        } catch (\Exception $e) {
            $response['message'] = __('We can\'t save the shopping list right now.');
        }

        return $resultJson->setData($response);
    }
}
