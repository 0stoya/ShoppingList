<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ShoppingListItemProduct implements ResolverInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['product_id'])) {
            return null;
        }

        try {
            // Retrieve the store ID from the GraphQL context to ensure accurate catalog visibility
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

            return $this->productRepository->getById(
                (int)$value['product_id'], 
                false, 
                $storeId
            );
        } catch (NoSuchEntityException $e) {
            // The product was deleted, disabled, or is not assigned to the current store view.
            // Returning null gracefully prevents the entire ShoppingListItem node from crashing.
            return null;
        }
    }
}
