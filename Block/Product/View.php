<?php
namespace TR\ShoppingList\Block\Product;

use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
// ✅ Use the new, correct ViewModel
use TR\ShoppingList\ViewModel\ListProvider;

class View extends Template
{
    protected $customerSession;
    protected $viewModel;
    protected $registry;

    public function __construct(
        Context $context,
        Session $customerSession,
        // ✅ Ask for the new ViewModel
        ListProvider $viewModel,
        Registry $registry,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->viewModel = $viewModel;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getViewModel(): ListProvider
    {
        return $this->viewModel;
    }

    /**
     * Do not render the block if the customer is not logged in
     */
    protected function _toHtml()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }
}