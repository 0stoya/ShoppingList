<?php
namespace 0stoya\ShoppingList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use 0stoya\ShoppingList\ViewModel\ListProvider; // <-- Use the new ViewModel

class Lists extends Template
{
    protected $viewModel;
    /**
     * @var \0stoya\ShoppingList\Model\ResourceModel\ShoppingList\Collection|null
     */
    protected $listCollection;
    public function __construct(
        Context $context,
        ListProvider $viewModel, // <-- Ask for the new ViewModel
        array $data = []
    ) 
    
    {
        $this->viewModel = $viewModel;
        parent::__construct($context, $data);
    }

    public function getViewModel(): ListProvider
    {
        return $this->viewModel;
    }
}