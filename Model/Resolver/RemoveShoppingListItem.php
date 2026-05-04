<?php
declare(strict_types=1);
namespace Ostoya\ShoppingList\Model\Resolver; use Magento\Framework\GraphQl\Config\Element\Field;use Magento\Framework\GraphQl\Query\ResolverInterface;use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;use Ostoya\ShoppingList\Model\Service\ShoppingListService;
class RemoveShoppingListItem implements ResolverInterface{public function __construct(private readonly CustomerContext $customerContext,private readonly ShoppingListService $service){} public function resolve(Field $field,$context,ResolveInfo $info,array $value=null,array $args=null): array{$id=(int)$args['input']['item_id'];$this->service->removeItem($this->customerContext->getCustomerId($context),$id);return ['success'=>true,'message'=>'Shopping list item removed.','removed_item_id'=>$id];}}
