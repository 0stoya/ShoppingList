<?php
namespace TR\ShoppingList\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use TR\CustomerPricing\Model\ResourceModel\Price\CollectionFactory as CustomPriceCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
// ✅ This is the correct path for the logger interface
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Api\StockStateInterface;

class ItemProvider implements ArgumentInterface
{
    private $customerSession;
    private $itemCollectionFactory;
    private $registry;
    private $productRepository;
    private $priceHelper;
    private $customerRepository;
    private $customPriceCollectionFactory;
    private $productCollectionFactory;
    private $logger;
    private $stockState;

    public function __construct(
        CustomerSession $customerSession,
        ItemCollectionFactory $itemCollectionFactory,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        PriceHelper $priceHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomPriceCollectionFactory $customPriceCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        StockStateInterface $stockState, 
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
        $this->customerRepository = $customerRepository;
        $this->customPriceCollectionFactory = $customPriceCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockState = $stockState;
        $this->logger = $logger;
    }

    public function getCurrentShoppingList()
    {
        return $this->registry->registry('current_shopping_list');
    }

    public function getListItems()
    {
        $list = $this->getCurrentShoppingList();
        if (!$list) {
            return null;
        }
        return $this->itemCollectionFactory->create()->addFieldToFilter('list_id', $list->getId());
    }
    
    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            $this->logger->error('Error loading product: ' . $e->getMessage());
            return null;
        }
    }

    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
    
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getProductCollection()
    {
        $productIds = [];
        $items = $this->getListItems();

        if (!$items) {
            return null;
        }

        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }

        if (empty($productIds)) {
            return null;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'units','meta_title']);

        return $collection;
    }

    public function getCustomerSpecificPrice(\Magento\Catalog\Api\Data\ProductInterface $product): float
    {
        if (!$this->customerSession->isLoggedIn()) {
            return (float) $product->getFinalPrice();
        }
        try {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerCodeAttribute = $customer->getCustomAttribute('accord_customer_code');
            if ($customerCodeAttribute && $customerCodeAttribute->getValue()) {
                $customerCode = $customerCodeAttribute->getValue();
                $priceCollection = $this->customPriceCollectionFactory->create();
                $priceCollection->addFieldToFilter('sku', $product->getSku())
                                ->addFieldToFilter('accord_customer_code', $customerCode);
                if ($priceCollection->getSize() > 0) {
                    return (float) $priceCollection->getFirstItem()->getPrice();
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting custom price: ' . $e->getMessage());
        }
        return (float) $product->getFinalPrice();
    }
    public function getStockStatusHtml(\Magento\Catalog\Api\Data\ProductInterface $product): string
    {
        $isSalable = $product->isSalable();
        
        if ($isSalable) {
            $class = 'in-stock';
            $label = __('In Stock');
        } else {
            $class = 'out-of-stock';
            $label = __('Out of Stock');
        }
        
        return "<div class='stock-status {$class}'><span>{$label}</span></div>";
    }
}