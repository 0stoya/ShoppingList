# ShoppingList

This repository contains a Magento 2 module allowing customers to maintain multiple shopping lists. Products can be added from the storefront and lists can be imported from the admin area using a CSV file. A simple frontend page displays a customer's lists in a modern table layout.

## Installation
Copy the contents of `app/code/TR/ShoppingList` into your Magento installation under the same path and run Magento setup commands:

```
bin/magento module:enable TR_ShoppingList
bin/magento setup:upgrade
```

## CSV format
The import expects a header row with the following columns:

```
customer_email,list_name,product_sku,qty
```

Each row represents a product to place in a customer's shopping list. Lists are created automatically if they do not already exist.

## Frontend page
After installation the shopping lists for a logged in customer can be viewed at `/shoppinglist/`. The page uses a lightweight template and CSS file located in `view/frontend` to present lists in a modern responsive table layout.
