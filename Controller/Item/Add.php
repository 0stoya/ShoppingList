<?php
namespace Ostoya\ShoppingList\Controller\Item;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Ostoya\ShoppingList\Model\Service\ShoppingListService;

class Add extends Action implements HttpPostActionInterface
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

        $listId = (int) $this->getRequest()->getParam('list_id');
        $productId = (int) $this->getRequest()->getParam('product_id');
        $qty = (float) $this->getRequest()->getParam('qty', 1);

        try {
            $this->shoppingListService->addItem(
                (int) $this->customerSession->getCustomerId(),
                $listId,
                $productId,
                null,
                $qty,
                'ERROR_IF_EXISTS'
            );

            $response['success'] = true;
            $response['message'] = __('The product was added to your shopping list.');
            $this->messageManager->addSuccessMessage($response['message']);
        } catch (GraphQlInputException $e) {
            $response['message'] = __('This product is already in the selected shopping list.');
            $this->messageManager->addWarningMessage($response['message']);
        } catch (\Exception $e) {
            $response['message'] = __('We can\'t add the item to the shopping list right now.');
        }

        return $resultJson->setData($response);
    }
}
