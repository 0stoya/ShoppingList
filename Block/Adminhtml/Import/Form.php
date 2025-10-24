<?php
namespace TR\ShoppingList\Block\Adminhtml\Import;

use Magento\Backend\Block\Template;

class Form extends Template
{
    protected $_template = 'TR_ShoppingList::import/form.phtml';

    public function getFormAction()
    {
        return $this->getUrl('shoppinglist/import/save');
    }
}
