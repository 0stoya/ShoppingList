<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory;
use Psr\Log\LoggerInterface;

class ItemProvider implements ArgumentInterface
{
    private ItemCollectionFactory $itemCollectionFactory;
    private Registry $registry;
    private ProductRepositoryInterface $productRepository;
    private PriceHelper $priceHelper;
    private ProductCollectionFactory $productCollectionFactory;
    private StockStateInterface $stockState;
    private LoggerInterface $logger;

    public function __construct(
        ItemCollectionFactory $itemCollectionFactory,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        PriceHelper $priceHelper,
        ProductCollectionFactory $productCollectionFactory,
        StockStateInterface $stockState,
        LoggerInterface $logger
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
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

        return $this->itemCollectionFactory
            ->create()
            ->addFieldToFilter('list_id', $list->getId());
    }

    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById((int) $productId);
        } catch (\Exception $e) {
            $this->logger->error('Error loading product: ' . $e->getMessage());
            return null;
        }
    }

    public function getFormattedPrice($price): string
    {
        return $this->priceHelper->currency((float) $price, true, false);
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
            $productIds[] = (int) $item->getProductId();
        }

        if (empty($productIds)) {
            return null;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect([
            'name',
            'price',
            'small_image',
            'units',
            'meta_title'
        ]);

        return $collection;
    }

    /**
     * Kept for template compatibility.
     */
    public function getCustomerSpecificPrice(ProductInterface $product): float
    {
        return (float) $product->getFinalPrice();
    }

    public function getStockStatusHtml(ProductInterface $product): string
    {
        if ($product->isSalable()) {
            return "<div class='stock-status in-stock'><span>" . __('In Stock') . "</span></div>";
        }

        return "<div class='stock-status out-of-stock'><span>" . __('Out of Stock') . "</span></div>";
    }
}
