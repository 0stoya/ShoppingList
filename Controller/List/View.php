<?php
namespace TR\ShoppingList\Controller\List;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session as CustomerSession;
use TR\ShoppingList\Model\ShoppingListFactory;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface; // <-- Add this logger

class View extends Action implements HttpGetActionInterface
{
    protected $customerSession;
    protected $registry;
    protected $shoppingListFactory;
    protected $logger; // <-- Add this property

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Registry $registry,
        ShoppingListFactory $shoppingListFactory,
        LoggerInterface $logger // <-- Inject the logger
    ) {
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->shoppingListFactory = $shoppingListFactory;
        $this->logger = $logger; // <-- Assign the logger
        parent::__construct($context);
    }

    public function execute()
    {
        $this->logger->info('SHOPPING LIST DEBUG: View Controller execute() started.');

        if (!$this->customerSession->authenticate()) {
            $this->logger->info('SHOPPING LIST DEBUG: Customer not authenticated. Redirecting.');
            $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            return null;
        }

        $listId = (int) $this->getRequest()->getParam('list_id');
        $this->logger->info('SHOPPING LIST DEBUG: Requested list_id is: ' . $listId);

        if (!$listId) {
            $this->messageManager->addErrorMessage(__('No shopping list ID was specified.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $list = $this->shoppingListFactory->create()->load($listId);
        $this->logger->info('SHOPPING LIST DEBUG: Loaded list with actual ID: ' . $list->getId());

        if (!$list->getId() || $list->getCustomerId() != $this->customerSession->getCustomerId()) {
            $this->logger->info('SHOPPING LIST DEBUG: List not found or customer ownership failed.');
            $this->messageManager->addErrorMessage(__('The requested shopping list was not found.'));
            return $this->resultRedirectFactory->create()->setPath('shoppinglist/index/index');
        }

        $this->logger->info('SHOPPING LIST DEBUG: List found, owned by customer. Registering list object.');
        $this->registry->register('current_shopping_list', $list);
        $this->logger->info('SHOPPING LIST DEBUG: List registration complete.');

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set($list->getListName());
        return $resultPage;
    }
}