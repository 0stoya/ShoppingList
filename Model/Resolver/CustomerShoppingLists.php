<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Ostoya\ShoppingList\Model\Service\ShoppingListService;

class CustomerShoppingLists implements ResolverInterface
{
    public function __construct(
        private readonly CustomerContext $customerContext,
        private readonly ShoppingListService $service,
        private readonly ResolverDataFormatter $formatter
    ) {}

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $cid = $this->customerContext->getCustomerId($context);
        $cp  = (int)($args['currentPage'] ?? 1);
        $ps  = (int)($args['pageSize'] ?? 20);

        if ($cp < 1 || $ps < 1) {
            throw new GraphQlInputException(__('Invalid pagination values.'));
        }

        $data  = $this->service->getCustomerLists($cid, $args['search'] ?? null, $cp, $ps);
        $items = [];

        foreach ($data['items'] as $list) {
            // Load items for each list (same as CustomerShoppingList does)
            $itemData      = $this->service->getListItems($cid, (int)$list->getId(), 1, 200);
            $formattedItems = [];
            foreach ($itemData['items'] as $item) {
                $formattedItems[] = $this->formatter->formatItem($item);
            }
            // Pass real item count from DB
            $listArray = $this->formatter->formatList($list, $formattedItems);
            $listArray['items_count'] = $itemData['total_count'];
            $items[] = $listArray;
        }

        return [
            'items'       => $items,
            'total_count' => $data['total_count'],
            'page_info'   => [
                'current_page' => $data['current_page'],
                'page_size'    => $data['page_size'],
                'total_pages'  => (int)ceil($data['total_count'] / max(1, $data['page_size'])),
            ],
        ];
    }
}
