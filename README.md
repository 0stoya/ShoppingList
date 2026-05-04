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

## GraphQL API

All shopping-list GraphQL operations require an authenticated customer token and are scoped to the current customer.

### Query customer lists

```graphql
query CustomerShoppingLists {
  customerShoppingLists(pageSize: 10, currentPage: 1) {
    total_count
    items {
      list_id
      list_name
      items_count
    }
    page_info {
      current_page
      page_size
      total_pages
    }
  }
}
```

### Create list

```graphql
mutation CreateShoppingList {
  createShoppingList(input: { list_name: "Weekly Order" }) {
    success
    message
    list {
      list_id
      list_name
    }
  }
}
```

### Add product to list

```graphql
mutation AddProductToShoppingList {
  addProductToShoppingList(
    input: {
      list_id: 1
      sku: "ABC-123"
      qty: 2
      mode: SET_OR_INCREMENT
    }
  ) {
    success
    message
    item {
      item_id
      sku
      qty
    }
  }
}
```

### Query list detail

```graphql
query CustomerShoppingList {
  customerShoppingList(list_id: 1) {
    list_id
    list_name
    items_count
    items {
      item_id
      product_id
      sku
      name
      qty
      product {
        sku
        name
      }
    }
  }
}
```
