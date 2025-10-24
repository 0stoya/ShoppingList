<?php
namespace TR\ShoppingList\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ProductRepository;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

class Add extends Action
{
    protected $customerSession;
    protected $productRepository;
    protected $listFactory;
    protected $itemFactory;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepository $productRepository,
        ShoppingListFactory $listFactory,
        ShoppingListItemFactory $itemFactory,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->listFactory = $listFactory;
        $this->itemFactory = $itemFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        if (!$this->customerSession->authenticate()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $productId = (int)$this->getRequest()->getParam('product_id');
        $listName = $this->getRequest()->getParam('list_name', 'Default');
        $customerId = $this->customerSession->getCustomerId();

        try {
            $product = $this->productRepository->getById($productId);
            $list = $this->listFactory->create();
            $collection = $list->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('name', $listName);
            if ($collection->getSize()) {
                $list = $collection->getFirstItem();
            } else {
                $list->setCustomerId($customerId);
                $list->setName($listName);
                $list->save();
            }
            $item = $this->itemFactory->create();
            $item->setListId($list->getId());
            $item->setProductId($product->getId());
            $item->setQty(1);
            $item->save();
            $this->messageManager->addSuccessMessage(__('Added to shopping list.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
