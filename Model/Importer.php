<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList as ListResource;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory;
use Psr\Log\LoggerInterface;

class Importer
{
    private CustomerRepositoryInterface $customerRepository;
    private ProductRepositoryInterface $productRepository;
    private ShoppingListFactory $listFactory;
    private ListResource $listResource;
    private ListCollectionFactory $listCollectionFactory;
    private ShoppingListItemFactory $itemFactory;
    private ItemResource $itemResource;
    private ItemCollectionFactory $itemCollectionFactory;
    private LoggerInterface $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        ShoppingListFactory $listFactory,
        ListResource $listResource,
        ListCollectionFactory $listCollectionFactory,
        ShoppingListItemFactory $itemFactory,
        ItemResource $itemResource,
        ItemCollectionFactory $itemCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->listFactory = $listFactory;
        $this->listResource = $listResource;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->logger = $logger;
    }

    public function validate(array $file): array
    {
        $fileHandler = $this->createFileHandler($file);
        $headers = (array) $fileHandler->fgetcsv();
        $report = ['errors' => [], 'valid_rows' => 0];

        foreach ($fileHandler as $rowNumber => $row) {
            if (!is_array($row) || count($row) < count($headers)) {
                continue;
            }

            $rowData = array_combine($headers, $row);
            if ($rowData === false) {
                continue;
            }

            $rowNum = $rowNumber + 2;
            $customerEmail = trim((string) ($rowData['customer_email'] ?? ''));
            $listName = trim((string) ($rowData['list_name'] ?? ''));
            $sku = trim((string) ($rowData['sku'] ?? ''));
            $isValid = true;

            try {
                $this->customerRepository->get($customerEmail);
            } catch (NoSuchEntityException $e) {
                $report['errors'][] = "Row {$rowNum}: Customer with email '{$customerEmail}' does not exist.";
                $isValid = false;
            }

            try {
                $this->productRepository->get($sku);
            } catch (NoSuchEntityException $e) {
                $report['errors'][] = "Row {$rowNum}: Product with SKU '{$sku}' does not exist.";
                $isValid = false;
            }

            if ($isValid && $listName !== '') {
                $report['valid_rows']++;
            } elseif ($isValid) {
                $report['errors'][] = "Row {$rowNum}: List name is required.";
            }
        }

        return $report;
    }

    public function import(array $file): array
    {
        $fileHandler = $this->createFileHandler($file);
        $headers = (array) $fileHandler->fgetcsv();
        $summary = ['success' => 0, 'skipped' => 0];
        $processedLists = [];

        foreach ($fileHandler as $rowNumber => $row) {
            if (!is_array($row) || count($row) < count($headers)) {
                continue;
            }

            $rowData = array_combine($headers, $row);
            if ($rowData === false) {
                $summary['skipped']++;
                continue;
            }

            $customerEmail = trim((string) ($rowData['customer_email'] ?? ''));
            $listName = trim((string) ($rowData['list_name'] ?? ''));
            $sku = trim((string) ($rowData['sku'] ?? ''));

            if ($customerEmail === '' || $listName === '' || $sku === '' || !is_numeric($rowData['qty'])) {
                $summary['skipped']++;
                continue;
            }

            try {
                $customer = $this->customerRepository->get($customerEmail);
                $product = $this->productRepository->get($sku);
                $listCacheKey = $customer->getId() . '-' . $listName;

                if (!isset($processedLists[$listCacheKey])) {
                    $processedLists[$listCacheKey] = $this->findOrCreateList((int) $customer->getId(), $listName);
                }

                $list = $processedLists[$listCacheKey];
                $itemCollection = $this->itemCollectionFactory->create();
                $itemCollection->addFieldToFilter('list_id', (int) $list->getId())
                    ->addFieldToFilter('product_id', (int) $product->getId());

                if ($itemCollection->getSize() > 0) {
                    $summary['skipped']++;
                    continue;
                } else {
                    $newItem = $this->itemFactory->create();
                    $newItem->setListId((int) $list->getId())
                        ->setProductId((int) $product->getId())
                        ->setQty((float) $rowData['qty']);
                    $this->itemResource->save($newItem);
                }

                $summary['success']++;
            } catch (\Exception $e) {
                $this->logger->error('Import Error on row ' . ($rowNumber + 2) . ': ' . $e->getMessage());
                $summary['skipped']++;
            }
        }

        return $summary;
    }

    private function findOrCreateList(int $customerId, string $listName): ShoppingList
    {
        $collection = $this->listCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('list_name', $listName);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }

        $list = $this->listFactory->create();
        $list->setCustomerId($customerId)->setListName($listName);
        $this->listResource->save($list);

        return $list;
    }

    private function createFileHandler(array $file): \SplFileObject
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }

        $fileHandler = new \SplFileObject($file['tmp_name'], 'r');
        $fileHandler->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        return $fileHandler;
    }
}
