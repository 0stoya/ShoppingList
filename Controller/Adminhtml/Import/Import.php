<?php
namespace TR\ShoppingList\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use TR\ShoppingList\Model\Importer;

class Import extends Action
{
    private $importer;
    
    public function __construct(Context $context, Importer $importer)
    {
        $this->importer = $importer;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                $file = $this->getRequest()->getFiles('import_file');
                $summary = $this->importer->import($file);
                $this->messageManager->addSuccessMessage(
                    __('%1 row(s) successfully imported. %2 row(s) skipped.', $summary['success'], $summary['skipped'])
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}