<?php
namespace TR\ShoppingList\Block\Index;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use Magento\Catalog\Model\ProductRepository;

class Lists extends Template
{
    protected $customerSession;
    protected $listFactory;
    protected $itemFactory;
    protected $productRepository;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        ShoppingListFactory $listFactory,
        ShoppingListItemFactory $itemFactory,
        ProductRepository $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->listFactory = $listFactory;
        $this->itemFactory = $itemFactory;
        $this->productRepository = $productRepository;
    }

    public function getLists()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }
        $customerId = $this->customerSession->getCustomerId();
        $listCollection = $this->listFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        foreach ($listCollection as $list) {
            $items = $this->itemFactory->create()->getCollection()
                ->addFieldToFilter('list_id', $list->getId());
            foreach ($items as $item) {
                try {
                    $product = $this->productRepository->getById($item->getProductId());
                    $item->setProduct($product);
                } catch (\Exception $e) {
                    continue;
                }
            }
            $list->setItems($items);
        }
        return $listCollection;
    }
}
