<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CleanupShoppingListDuplicates implements DataPatchInterface
{
    public function __construct(private readonly ModuleDataSetupInterface $moduleDataSetup)
    {
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $listTable = $this->moduleDataSetup->getTable('tr_shopping_list');
        $itemTable = $this->moduleDataSetup->getTable('tr_shopping_list_item');

        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            $duplicateLists = $connection->fetchAll(
                "SELECT customer_id, list_name, MIN(list_id) AS keep_id FROM {$listTable} GROUP BY customer_id, list_name HAVING COUNT(*) > 1"
            );

            foreach ($duplicateLists as $row) {
                $ids = $connection->fetchCol(
                    $connection->select()->from($listTable, ['list_id'])
                        ->where('customer_id = ?', (int) $row['customer_id'])
                        ->where('list_name = ?', (string) $row['list_name'])
                        ->where('list_id <> ?', (int) $row['keep_id'])
                );

                foreach ($ids as $duplicateListId) {
                    $connection->update($itemTable, ['list_id' => (int) $row['keep_id']], ['list_id = ?' => (int) $duplicateListId]);
                    $connection->delete($listTable, ['list_id = ?' => (int) $duplicateListId]);
                }
            }

            $duplicateItems = $connection->fetchAll(
                "SELECT list_id, product_id, MIN(item_id) AS keep_item_id, SUM(qty) AS total_qty FROM {$itemTable} GROUP BY list_id, product_id HAVING COUNT(*) > 1"
            );

            foreach ($duplicateItems as $row) {
                $connection->update($itemTable, ['qty' => (float) $row['total_qty']], ['item_id = ?' => (int) $row['keep_item_id']]);
                $connection->delete($itemTable, [
                    'list_id = ?' => (int) $row['list_id'],
                    'product_id = ?' => (int) $row['product_id'],
                    'item_id <> ?' => (int) $row['keep_item_id'],
                ]);
            }
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
