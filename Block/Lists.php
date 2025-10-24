<?php
namespace TR\ShoppingList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use TR\ShoppingList\ViewModel\ListProvider; // <-- Use the new ViewModel

class Lists extends Template
{
    protected $viewModel;
    /**
     * @var \TR\ShoppingList\Model\ResourceModel\ShoppingList\Collection|null
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