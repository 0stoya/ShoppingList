<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Phrase;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList as ShoppingListResource;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem as ShoppingListItemResource;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory;
use Ostoya\ShoppingList\Model\ShoppingListFactory;
use Ostoya\ShoppingList\Model\ShoppingListItemFactory;

class ShoppingListService
{
    public function __construct(
        private readonly ListCollectionFactory $listCollectionFactory,
        private readonly ItemCollectionFactory $itemCollectionFactory,
        private readonly ShoppingListFactory $shoppingListFactory,
        private readonly ShoppingListItemFactory $shoppingListItemFactory,
        private readonly ShoppingListResource $shoppingListResource,
        private readonly ShoppingListItemResource $shoppingListItemResource,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function getCustomerLists(int $customerId, ?string $search = null, int $currentPage = 1, int $pageSize = 20): array
    {
        $collection = $this->listCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        if ($search !== null && trim($search) !== '') {
            $collection->addFieldToFilter('list_name', ['like' => '%' . trim($search) . '%']);
        }
        $collection->setCurPage(max(1, $currentPage));
        $collection->setPageSize(max(1, $pageSize));

        return ['items' => $collection->getItems(), 'total_count' => (int)$collection->getSize(), 'current_page' => (int)$collection->getCurPage(), 'page_size' => (int)$collection->getPageSize()];
    }

    public function getCustomerListById(int $customerId, int $listId)
    {
        $list = $this->shoppingListFactory->create();
        $this->shoppingListResource->load($list, $listId);
        if (!(int)$list->getId() || (int)$list->getData('customer_id') !== $customerId) {
            throw new NoSuchEntityException(__('Shopping list not found.'));
        }
        return $list;
    }

    public function createList(int $customerId, string $listName)
    {
        $list = $this->shoppingListFactory->create();
        $list->setData('customer_id', $customerId);
        $list->setData('list_name', $this->validateListName($listName));
        $this->shoppingListResource->save($list);
        return $list;
    }

    public function renameList(int $customerId, int $listId, string $listName)
    {
        $list = $this->getCustomerListById($customerId, $listId);
        $list->setData('list_name', $this->validateListName($listName));
        $this->shoppingListResource->save($list);
        return $list;
    }

    public function deleteList(int $customerId, int $listId): void
    {
        $this->shoppingListResource->delete($this->getCustomerListById($customerId, $listId));
    }

    public function addItem(int $customerId, int $listId, ?int $productId, ?string $sku, float $qty, string $mode = 'SET_OR_INCREMENT')
    {
        $list = $this->getCustomerListById($customerId, $listId);
        $qty = $this->validateQty($qty);
        $product = $productId ? $this->productRepository->getById($productId) : $this->productRepository->get((string)$sku);
        $existing = $this->itemCollectionFactory->create()
            ->addFieldToFilter('list_id', (int)$list->getId())
            ->addFieldToFilter('product_id', (int)$product->getId())
            ->getFirstItem();

        if ((int)$existing->getId()) {
            if ($mode === 'ERROR_IF_EXISTS') {
                throw new GraphQlInputException(new Phrase('Product already exists in this list.'));
            }
            $existing->setData('qty', $mode === 'REPLACE_QTY' ? $qty : ((float)$existing->getData('qty') + $qty));
            $this->shoppingListItemResource->save($existing);
            return $existing;
        }

        $item = $this->shoppingListItemFactory->create();
        $item->setData('list_id', (int)$list->getId());
        $item->setData('product_id', (int)$product->getId());
        $item->setData('qty', $qty);
        $this->shoppingListItemResource->save($item);
        return $item;
    }

    public function updateItemQty(int $customerId, int $itemId, float $qty)
    {
        $item = $this->getCustomerItemById($customerId, $itemId);
        $item->setData('qty', $this->validateQty($qty));
        $this->shoppingListItemResource->save($item);
        return $item;
    }

    public function removeItem(int $customerId, int $itemId): void
    {
        $this->shoppingListItemResource->delete($this->getCustomerItemById($customerId, $itemId));
    }

    public function getListItems(int $customerId, int $listId, int $currentPage = 1, int $pageSize = 20): array
    {
        $list = $this->getCustomerListById($customerId, $listId);
        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('list_id', (int)$list->getId());
        $collection->setCurPage(max(1, $currentPage));
        $collection->setPageSize(max(1, $pageSize));
        return ['items' => $collection->getItems(), 'total_count' => (int)$collection->getSize()];
    }

    public function getCustomerItemById(int $customerId, int $itemId)
    {
        $item = $this->shoppingListItemFactory->create();
        $this->shoppingListItemResource->load($item, $itemId);
        if (!(int)$item->getId()) {
            throw new NoSuchEntityException(__('Shopping list item not found.'));
        }
        $this->getCustomerListById($customerId, (int)$item->getData('list_id'));
        return $item;
    }

    private function validateListName(string $listName): string
    {
        $listName = trim($listName);
        if ($listName === '' || mb_strlen($listName) > 255) {
            throw new GraphQlInputException(new Phrase('List name must be between 1 and 255 characters.'));
        }
        return $listName;
    }

    private function validateQty(float $qty): float
    {
        if ($qty <= 0) {
            throw new GraphQlInputException(new Phrase('Quantity must be greater than zero.'));
        }
        return $qty;
    }
}
