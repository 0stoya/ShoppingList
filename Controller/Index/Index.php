<?php
namespace TR\ShoppingList\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->authenticate()) {
            $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            return null;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}