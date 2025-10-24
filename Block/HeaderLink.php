<?php
namespace TR\ShoppingList\Block;

use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DefaultPathInterface; // <-- Add this

class HeaderLink extends Current
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath, // <-- Accept the required dependency
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        // ✅ Pass all required arguments to the parent constructor
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }
}