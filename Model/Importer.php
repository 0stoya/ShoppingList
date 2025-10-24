<?php
namespace TR\ShoppingList\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingList as ListResource;
use TR\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem as ItemResource;
use TR\ShoppingList\Model\ResourceModel\ShoppingListItem\CollectionFactory as ItemCollectionFactory; // <-- Add this
use Psr\Log\LoggerInterface;

class Importer
{
    protected $customerRepository;
    protected $productRepository;
    protected $listFactory;
    protected $listResource;
    protected $listCollectionFactory;
    protected $itemFactory;
    protected $itemResource;
    protected $itemCollectionFactory; // <-- Add this
    protected $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        ShoppingListFactory $listFactory,
        ListResource $listResource,
        ListCollectionFactory $listCollectionFactory,
        ShoppingListItemFactory $itemFactory,
        ItemResource $itemResource,
        ItemCollectionFactory $itemCollectionFactory, // <-- Inject this
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->listFactory = $listFactory;
        $this->listResource = $listResource;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->itemCollectionFactory = $itemCollectionFactory; // <-- Assign this
        $this->logger = $logger;
    }
public function validate($file): array
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        $fileHandler = new \SplFileObject($file['tmp_name'], 'r');
        $fileHandler->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        
        $headers = $fileHandler->fgetcsv();
        $report = ['errors' => [], 'valid_rows' => 0];

        foreach ($fileHandler as $rowNumber => $row) {
            if (count($row) < count($headers)) continue;
            $rowData = array_combine($headers, $row);

            $isValid = true;
            $rowNumForError = $rowNumber + 2;

            try {
                // Validate customer exists
                $this->customerRepository->get($rowData['customer_email']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $report['errors'][] = "Row {$rowNumForError}: Customer with email '{$rowData['customer_email']}' does not exist.";
                $isValid = false;
            }

            try {
                // Validate SKU exists
                $this->productRepository->get($rowData['sku']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $report['errors'][] = "Row {$rowNumForError}: Product with SKU '{$rowData['sku']}' does not exist.";
                $isValid = false;
            }
            
            if ($isValid) {
                $report['valid_rows']++;
            }
        }
        return $report;
    }
    public function import($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }
        
        // Use SplFileObject for better memory management with large files
        $fileHandler = new \SplFileObject($file['tmp_name'], 'r');
        $fileHandler->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        
        $headers = $fileHandler->fgetcsv();
        
        $summary = ['success' => 0, 'skipped' => 0];
        $processedLists = [];

        foreach ($fileHandler as $rowNumber => $row) {
            if (count($row) < count($headers)) {
                continue; // Skip empty or malformed rows
            }

            $rowData = array_combine($headers, $row);

            if (empty($rowData['customer_email']) || empty($rowData['list_name']) || empty($rowData['sku']) || !is_numeric($rowData['qty'])) {
                $summary['skipped']++;
                continue;
            }

            try {
                $customer = $this->customerRepository->get($rowData['customer_email']);
                $product = $this->productRepository->get($rowData['sku']);
                
                $listCacheKey = $customer->getId() . '-' . $rowData['list_name'];
                
                if (!isset($processedLists[$listCacheKey])) {
                    $list = $this->findOrCreateList($customer->getId(), $rowData['list_name']);
                    $processedLists[$listCacheKey] = $list;
                }
                $list = $processedLists[$listCacheKey];
                
                // ✅ START: Logic to handle duplicate SKUs
                $itemCollection = $this->itemCollectionFactory->create();
                $itemCollection->addFieldToFilter('list_id', $list->getId())
                             ->addFieldToFilter('product_id', $product->getId());

                if ($itemCollection->getSize() > 0) {
                    // Item already exists, so we update its quantity
                    $existingItem = $itemCollection->getFirstItem();
                    $newQty = $existingItem->getQty() + (float)$rowData['qty'];
                    $existingItem->setQty($newQty);
                    $this->itemResource->save($existingItem);
                } else {
                    // Item is new, so we create it
                    $newItem = $this->itemFactory->create();
                    $newItem->setListId($list->getId())
                           ->setProductId($product->getId())
                           ->setQty((float)$rowData['qty']);
                    $this->itemResource->save($newItem);
                }
                // ✅ END: Logic to handle duplicate SKUs
                
                $summary['success']++;

            } catch (\Exception $e) {
                $this->logger->error('Import Error on row ' . ($rowNumber + 2) . ': ' . $e->getMessage());
                $summary['skipped']++;
            }
        }

        return $summary;
    }
    
    private function findOrCreateList($customerId, $listName)
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
}