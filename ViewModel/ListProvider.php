<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\ViewModel;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList\Collection;
use Ostoya\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;

class ListProvider implements ArgumentInterface
{
    private CustomerSession $customerSession;
    private ListCollectionFactory $listCollectionFactory;
    private Registry $registry;
    private RequestInterface $request;

    public function __construct(
        CustomerSession $customerSession,
        ListCollectionFactory $listCollectionFactory,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->registry = $registry;
        $this->request = $request;
    }

    public function getCustomerLists()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        /** @var Collection $collection */
        $collection = $this->listCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', (int) $this->customerSession->getCustomerId());

        $searchQuery = $this->getSearchQuery();
        if ($searchQuery !== '') {
            $collection->addFieldToFilter('list_name', ['like' => '%' . $this->escapeSearchTerm($searchQuery) . '%']);
        }

        return $collection;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getSearchQuery(): string
    {
        return trim((string) $this->request->getParam('q', ''));
    }

    private function escapeSearchTerm(string $term): string
    {
        return addcslashes($term, '%_');
    }
}
