<?php
namespace TR\ShoppingList\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ResourceModel\ShoppingList\CollectionFactory as ListCollectionFactory;
use Magento\Framework\Registry; // <-- Add this
use Magento\Framework\App\RequestInterface;

class ListProvider implements ArgumentInterface
{
    private $customerSession;
    private $listCollectionFactory;
    private $registry; // <-- Add this
    private $request;

    public function __construct(
        CustomerSession $customerSession,
        ListCollectionFactory $listCollectionFactory,
        Registry $registry, // <-- Inject this
        RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->listCollectionFactory = $listCollectionFactory;
        $this->registry = $registry; // <-- Assign this
        $this->request = $request;
    }

    /**
     * @return \TR\ShoppingList\Model\ResourceModel\ShoppingList\Collection|array
     */
    public function getCustomerLists()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }
        $collection = $this->listCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());

        $searchQuery = $this->getSearchQuery();
        if ($searchQuery !== '') {
            $collection->addFieldToFilter(
                'list_name',
                ['like' => '%' . $this->escapeSearchTerm($searchQuery) . '%']
            );
        }

        return $collection;
    }

    /**
     * ✅ NEW METHOD
     * Get the current product from Magento's registry.
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Return the trimmed search term from the request.
     */
    public function getSearchQuery(): string
    {
        $query = (string) $this->request->getParam('q', '');

        return trim($query);
    }

    /**
     * Escape SQL wildcard characters used in LIKE conditions.
     */
    private function escapeSearchTerm(string $term): string
    {
        return addcslashes($term, '%_');
    }
}