<?php
namespace TR\ShoppingList\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory; // <-- Add this
use Magento\Framework\Controller\Result\JsonFactory;

class Add extends Action implements HttpPostActionInterface
{
    protected $customerSession;
    protected $shoppingListFactory;
    protected $itemFactory;
    protected $itemResource;
    protected $itemCollectionFactory; // <-- Add this
    protected $jsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShoppingListFactory $shoppingListFactory,
        ShoppingListItemFactory $itemFactory,
        ItemResource $itemResource,
        ItemCollectionFactory $itemCollectionFactory, // <-- Inject this
        JsonFactory $jsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->shoppingListFactory = $shoppingListFactory;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->itemCollectionFactory = $itemCollectionFactory; // <-- Assign this
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

        $listId = (int) $this->getRequest()->getParam('list_id');
        $productId = (int) $this->getRequest()->getParam('product_id');
        $qty = (float) $this->getRequest()->getParam('qty', 1);

        $list = $this->shoppingListFactory->create()->load($listId);

        if (!$list->getId() || $list->getCustomerId() != $this->customerSession->getCustomerId()) {
            $response['message'] = __('The selected shopping list was not found.');
            return $resultJson->setData($response);
        }

        try {
            // ✅ START: Logic to check for duplicates
            $existingItems = $this->itemCollectionFactory->create();
            $existingItems->addFieldToFilter('list_id', $listId)
                          ->addFieldToFilter('product_id', $productId);

            if ($existingItems->getSize() > 0) {
                // The item is already in the list
                $response['message'] = __('This item is already in your "%1" list.', $list->getListName());
                // We still count this as a "success" for user feedback purposes
                $response['success'] = true; 
                $this->messageManager->addWarningMessage($response['message']);
            } else {
                // The item is new, so we add it
                $item = $this->itemFactory->create();
                $item->setListId($listId)
                     ->setProductId($productId)
                     ->setQty($qty);

                $this->itemResource->save($item);
                
                $response['success'] = true;
                $response['message'] = __('The product was added to your "%1" shopping list.', $list->getListName());
                $this->messageManager->addSuccessMessage($response['message']);
            }
            // ✅ END: Logic to check for duplicates

        } catch (\Exception $e) {
            $response['message'] = __('We can\'t add the item to the shopping list right now.');
        }

        return $resultJson->setData($response);
    }
}