# ShoppingList

Magento 2 module `ostoya/module-shopping-list` (`Ostoya_ShoppingList`) for managing customer shopping lists and importing list items by CSV.

## Installation

### Composer

```bash
composer require ostoya/module-shopping-list
bin/magento module:enable Ostoya_ShoppingList
bin/magento setup:upgrade
```

### app/code

Copy this repository to `app/code/Ostoya/ShoppingList` and run:

```bash
bin/magento module:enable Ostoya_ShoppingList
bin/magento setup:upgrade
```

## CSV import format

Required header:

```
customer_email,list_name,sku,qty
```

Lists are matched by `customer_email + list_name`; missing lists are created automatically. Duplicate SKUs in the same list are merged by increasing quantity.

## Frontend routes

- `/shoppinglist/`
- `/shoppinglist/list/view?list_id=...`

## Notes

- Database tables remain `tr_shopping_list` and `tr_shopping_list_item`.
- No dependency on legacy custom customer pricing modules.
