<?php
namespace TR\ShoppingList\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\Csv as CsvReader;
use Magento\Customer\Model\CustomerFactory;
use Magento\Catalog\Model\ProductRepository;
use TR\ShoppingList\Model\ShoppingListFactory;
use TR\ShoppingList\Model\ShoppingListItemFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'TR_ShoppingList::shoppinglist';

    protected $csvReader;
    protected $customerFactory;
    protected $productRepository;
    protected $listFactory;
    protected $itemFactory;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        CsvReader $csvReader,
        CustomerFactory $customerFactory,
        ProductRepository $productRepository,
        ShoppingListFactory $listFactory,
        ShoppingListItemFactory $itemFactory,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->csvReader = $csvReader;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->listFactory = $listFactory;
        $this->itemFactory = $itemFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $file = $this->getRequest()->getFiles('import_file');
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->messageManager->addErrorMessage(__('Invalid file upload'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $rows = $this->csvReader->getData($file['tmp_name']);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = array_combine($header, $row);
                $customer = $this->customerFactory->create()->getCollection()
                    ->addAttributeToFilter('email', $data['customer_email'])
                    ->getFirstItem();
                if (!$customer || !$customer->getId()) {
                    continue;
                }
                $listName = $data['list_name'];
                $list = $this->listFactory->create();
                $collection = $list->getCollection()
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->addFieldToFilter('name', $listName);
                if ($collection->getSize()) {
                    $list = $collection->getFirstItem();
                } else {
                    $list->setCustomerId($customer->getId());
                    $list->setName($listName);
                    $list->save();
                }
                try {
                    $product = $this->productRepository->get($data['product_sku']);
                    $item = $this->itemFactory->create();
                    $item->setListId($list->getId());
                    $item->setProductId($product->getId());
                    $item->setQty($data['qty']);
                    $item->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
            $this->messageManager->addSuccessMessage(__('Import completed.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
