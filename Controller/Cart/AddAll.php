<?php
namespace TR\ShoppingList\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use TR\ShoppingList\Model\ShoppingListFactory;
use Magento\Framework\Controller\Result\JsonFactory; // <-- Add this

class AddAll extends Action implements HttpPostActionInterface
{
    protected $customerSession;
    protected $cart;
    protected $productRepository;
    protected $itemFactory;
    protected $listFactory;
    protected $jsonFactory; // <-- Add this

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        ShoppingListItemFactory $itemFactory,
        ShoppingListFactory $listFactory,
        JsonFactory $jsonFactory // <-- Inject this
    ) {
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->itemFactory = $itemFactory;
        $this->listFactory = $listFactory;
        $this->jsonFactory = $jsonFactory; // <-- Assign this
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
        
        $itemIds = $this->getRequest()->getParam('selected_items', []);

        if (empty($itemIds)) {
            $response['message'] = __('Please select items to add to your cart.');
            $this->messageManager->addWarningMessage($response['message']);
            return $resultJson->setData($response);
        }

        try {
            $itemsAdded = 0;
            foreach ($itemIds as $itemId) {
                $item = $this->itemFactory->create()->load($itemId);
                
                $list = $this->listFactory->create()->load($item->getListId());
                if (!$item->getId() || $list->getCustomerId() != $this->customerSession->getCustomerId()) {
                    continue;
                }
                
                $product = $this->productRepository->getById($item->getProductId());
                $this->cart->addProduct($product, ['qty' => $item->getQty()]);
                $itemsAdded++;
            }
            $this->cart->save();

            if ($itemsAdded > 0) {
                $response['success'] = true;
                $response['message'] = __('%1 item(s) have been added to your shopping cart.', $itemsAdded);
                $this->messageManager->addSuccessMessage($response['message']);
            } else {
                 $response['message'] = __('No valid items were selected to be added to the cart.');
                 $this->messageManager->addWarningMessage($response['message']);
            }
        } catch (\Exception $e) {
            $response['message'] = __('We couldn\'t add the items to your cart.');
            $this->messageManager->addErrorMessage($response['message']);
        }
        
        return $resultJson->setData($response);
    }
}