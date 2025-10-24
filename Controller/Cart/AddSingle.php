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
use Magento\Framework\Controller\Result\JsonFactory;

class AddSingle extends Action implements HttpPostActionInterface
{
    protected $customerSession;
    protected $cart;
    protected $productRepository;
    protected $itemFactory;
    protected $listFactory;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        ShoppingListItemFactory $itemFactory,
        ShoppingListFactory $listFactory,
        JsonFactory $jsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->itemFactory = $itemFactory;
        $this->listFactory = $listFactory;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $response = ['success' => false];

        if (!$this->getRequest()->isAjax() || !$this->customerSession->authenticate(false)) {
            $response['message'] = __('Invalid request.');
            $this->messageManager->addErrorMessage($response['message']);
            return $resultJson->setData($response);
        }

        $itemId = (int) $this->getRequest()->getParam('item_id');
        $qty = (float) $this->getRequest()->getParam('qty', 1);

        if ($qty <= 0) {
            $response['message'] = __('Please enter a quantity greater than 0.');
            $this->messageManager->addErrorMessage($response['message']);
            return $resultJson->setData($response);
        }

        $item = $this->itemFactory->create()->load($itemId);

        // Security Check
        $list = $this->listFactory->create()->load($item->getListId());
        if (!$item->getId() || !$list->getId() || $list->getCustomerId() != $this->customerSession->getCustomerId()) {
            $response['message'] = __('This item could not be found.');
            $this->messageManager->addErrorMessage($response['message']);
            return $resultJson->setData($response);
        }

        try {
            $product = $this->productRepository->getById($item->getProductId());
            
            // Use the quantity from the request when adding to the cart
            $this->cart->addProduct($product, ['qty' => $qty]);
            $this->cart->save();
            
            $response['success'] = true;
            $response['message'] = __('You added %1 x %2 to your shopping cart.', $qty, $product->getName());
            $this->messageManager->addSuccessMessage($response['message']);

        } catch (\Exception $e) {
            $response['message'] = __('We can\'t add the item to the cart right now.');
            $this->messageManager->addErrorMessage($response['message']);
        }

        return $resultJson->setData($response);
    }
}