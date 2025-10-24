<?php
namespace TR\ShoppingList\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use TR\ShoppingList\Model\Importer;

class Validate extends Action
{
    private $importer;

    public function __construct(Context $context, Importer $importer)
    {
        $this->importer = $importer;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/');
        if (!$this->getRequest()->isPost()) {
            return $resultRedirect;
        }

        try {
            $file = $this->getRequest()->getFiles('import_file');
            $report = $this->importer->validate($file);

            if (empty($report['errors'])) {
                $this->messageManager->addSuccessMessage(
                    __('Validation successful. Found %1 valid row(s). The file is ready to be imported.', $report['valid_rows'])
                );
            } else {
                $this->messageManager->addWarningMessage(
                    __('Validation found %1 error(s) and %2 valid row(s). Please correct the errors and try again.', count($report['errors']), $report['valid_rows'])
                );
                foreach ($report['errors'] as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}