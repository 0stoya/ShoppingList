<?php
declare(strict_types=1);
namespace Ostoya\ShoppingList\Model\Resolver;
use Magento\Catalog\Api\ProductRepositoryInterface;use Magento\Framework\GraphQl\Config\Element\Field;use Magento\Framework\GraphQl\Query\ResolverInterface;use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
class ShoppingListItemProduct implements ResolverInterface{public function __construct(private readonly ProductRepositoryInterface $productRepository){} public function resolve(Field $field,$context,ResolveInfo $info,array $value=null,array $args=null){if(!isset($value['product_id'])){return null;}return $this->productRepository->getById((int)$value['product_id']);}}
