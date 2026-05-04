<?php
declare(strict_types=1);
namespace Ostoya\ShoppingList\Model\Resolver; use Magento\Framework\GraphQl\Config\Element\Field;use Magento\Framework\GraphQl\Query\ResolverInterface;use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;use Ostoya\ShoppingList\Model\Service\ShoppingListService;
class DeleteShoppingList implements ResolverInterface{public function __construct(private readonly CustomerContext $customerContext,private readonly ShoppingListService $service){} public function resolve(Field $field,$context,ResolveInfo $info,array $value=null,array $args=null): array{$listId=(int)$args['input']['list_id'];$this->service->deleteList($this->customerContext->getCustomerId($context),$listId);return ['success'=>true,'message'=>'Shopping list deleted.','deleted_list_id'=>$listId];}}
