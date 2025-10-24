<?php
namespace TR\ShoppingList\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use Magento\Framework\Controller\Result\JsonFactory;

class Update extends Action implements HttpPostActionInterface
{
    protected $customerSession;
    protected $listFactory;
    protected $itemFactory;
    protected $itemResource;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShoppingListFactory $listFactory,
        ShoppingListItemFactory $itemFactory,
        ItemResource $itemResource,
        JsonFactory $jsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->listFactory = $listFactory;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $response = ['success' => false];

        if (!$this->getRequest()->isPost() || !$this->customerSession->authenticate(false)) {
            $response['message'] = __('Invalid request.');
            $this->messageManager->addErrorMessage($response['message']);
            return $resultJson->setData($response);
        }

        $qtys = $this->getRequest()->getParam('qtys', []);
        
        try {
            $updatedItems = 0;
            foreach ($qtys as $itemId => $qty) {
                $item = $this->itemFactory->create()->load((int) $itemId);
                
                // Security check
                $list = $this->listFactory->create()->load($item->getListId());
                if ($item->getId() && $list->getCustomerId() == $this->customerSession->getCustomerId()) {
                    if ((float)$qty <= 0) {
                        $this->itemResource->delete($item);
                    } else {
                        $item->setQty((float) $qty);
                        $this->itemResource->save($item);
                    }
                    $updatedItems++;
                }
            }

            if ($updatedItems > 0) {
                $response['success'] = true;
                $response['message'] = __('%1 item(s) have been updated.', $updatedItems);
                $this->messageManager->addSuccessMessage($response['message']);
            } else {
                $response['message'] = __('No items were updated.');
                $this->messageManager->addWarningMessage($response['message']);
            }
        } catch (\Exception $e) {
            $response['message'] = __('We can\'t update the shopping list right now.');
            $this->messageManager->addErrorMessage($response['message']);
        }

        return $resultJson->setData($response);
    }
}