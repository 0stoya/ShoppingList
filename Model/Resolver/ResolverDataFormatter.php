<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;

class ResolverDataFormatter
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository) {}

    public function formatItem($item): array
    {
        $productId = (int)$item->getData('product_id');
        $sku = null; $name = null;
        try { $product = $this->productRepository->getById($productId); $sku = $product->getSku(); $name = $product->getName(); } catch (\Throwable $e) {}
        return ['item_id'=>(int)$item->getId(),'list_id'=>(int)$item->getData('list_id'),'product_id'=>$productId,'sku'=>$sku,'name'=>$name,'qty'=>(float)$item->getData('qty')];
    }

    public function formatList($list, array $items = []): array
    {
        return ['list_id'=>(int)$list->getId(),'list_name'=>(string)$list->getData('list_name'),'items_count'=>(int)($list->getData('items_count') ?? count($items)),'items'=>$items,'created_at'=>$list->getData('created_at'),'updated_at'=>$list->getData('updated_at')];
    }
}
