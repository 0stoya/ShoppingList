<?php
declare(strict_types=1);
namespace Ostoya\ShoppingList\Model\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;use Magento\Framework\GraphQl\Query\ResolverInterface;use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;use Ostoya\ShoppingList\Model\Service\ShoppingListService;
class CustomerShoppingList implements ResolverInterface{public function __construct(private readonly CustomerContext $customerContext,private readonly ShoppingListService $service,private readonly ResolverDataFormatter $formatter){}
public function resolve(Field $field,$context,ResolveInfo $info,array $value=null,array $args=null): array{$cid=$this->customerContext->getCustomerId($context);$list=$this->service->getCustomerListById($cid,(int)$args['list_id']);$itemData=$this->service->getListItems($cid,(int)$list->getId(),(int)($args['currentPage']??1),(int)($args['pageSize']??20));$items=[];foreach($itemData['items'] as $item){$items[]=$this->formatter->formatItem($item);}return $this->formatter->formatList($list,$items);}}
