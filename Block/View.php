<?php 
namespace TR\ShoppingList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use TR\ShoppingList\ViewModel\ItemProvider;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;

class View extends Template
{
    protected $viewModel;
    protected $imageHelper;
    protected $productCollection;

    public function __construct(
        Context $context,
        ItemProvider $viewModel,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->viewModel = $viewModel;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getViewModel()) {
            $collection = $this->getViewModel()->getProductCollection();

            if ($collection) {
                $this->productCollection = $collection;

                /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar */
                $toolbar = $this->getLayout()->getBlock('shopping_list_toolbar');
                if ($toolbar) {
                    // Configure limits and default per page
                    $toolbar->setAvailableLimit([10 => 10, 25 => 25, 50 => 50, 100 => 100]);
                    $toolbar->setDefaultLimit(25); // Change 25 to your preferred default
                    $toolbar->setCollection($this->productCollection);

                    // Get the pager inside the toolbar (if present) and wire it too
                    $pager = $toolbar->getChildBlock('product_list_toolbar_pager');
                    if ($pager) {
        $pager->setAvailableLimit([10 => 10, 25 => 25, 50 => 50, 100 => 100]);
        $pager->setCollection($this->productCollection);
                    }
                }

                // ALSO wire the standalone pager if present (optional, but best for explicit pager below items)
                $pager = $this->getLayout()->getBlock('shopping_list_pager');
if ($pager) {
    $pager->setAvailableLimit([10 => 10, 25 => 25, 50 => 50, 100 => 100]);
    $pager->setCollection($this->productCollection);
}
            }
        }
        return $this;
    }

    public function getProductCollection()
    {
        return $this->productCollection;
    }

    public function getItemByProductId($productId)
    {
        if ($this->getViewModel() && $this->getViewModel()->getListItems()) {
            foreach ($this->getViewModel()->getListItems() as $item) {
                if ($item->getProductId() == $productId) {
                    return $item;
                }
            }
        }
        return null;
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('shopping_list_toolbar');
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('shopping_list_pager');
    }

    public function getViewModel(): ItemProvider
    {
        return $this->viewModel;
    }
    
    public function getImageUrl(Product $product)
    {
        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')->resize(100, 100)->getUrl();
    }
}