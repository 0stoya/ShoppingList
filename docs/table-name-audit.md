# Shopping List Table Name Audit (`Ostoya_ShoppingList`)

## Scope and intent
This audit reviews current usage of legacy table names (`tr_shopping_list`, `tr_shopping_list_item`) and checks for any active usage of prospective names (`ostoya_shopping_list`, `ostoya_shopping_list_item`).

This report **does not** rename database tables and is focused on backward-safe recommendations.

## 1) Current table usage

### References to `tr_shopping_list`

1. `etc/db_schema.xml`
   - Defines primary shopping list table name and FK references.
   - **Expected**: canonical declarative schema definition.

2. `Model/ResourceModel/ShoppingList.php`
   - Resource model binds `ShoppingList` model to `tr_shopping_list`.
   - **Expected**: standard Magento resource model initialization.

3. `Model/ResourceModel/ListEntity.php`
   - Alternative/legacy resource model class also binds to `tr_shopping_list`.
   - **Expected but should be reviewed**: appears to be a compatibility/alias class; acceptable if intentionally retained.

4. `Setup/Patch/Data/CleanupShoppingListDuplicates.php`
   - Resolves full table name via `$moduleDataSetup->getTable('tr_shopping_list')` for deduplication patch.
   - **Expected**: setup patch should operate against installed table name; uses table-prefix-safe API.

5. `etc/db_schema.xml` constraints referencing `tr_shopping_list`
   - Child table foreign key points to `tr_shopping_list`.
   - **Expected**: relational integrity between list and list items.

6. `README.md`
   - Explicitly documents that table names remain `tr_`.
   - **Expected**: this is helpful and should remain explicit.

### References to `tr_shopping_list_item`

1. `etc/db_schema.xml`
   - Defines list-item table and FKs.
   - **Expected**.

2. `Model/ResourceModel/ShoppingListItem.php`
   - Resource model binds to `tr_shopping_list_item`.
   - **Expected**.

3. `Model/ResourceModel/ShoppingList/Collection.php`
   - Join for item count uses `$this->getTable('tr_shopping_list_item')`.
   - **Expected** and table-prefix-safe.

4. `Setup/Patch/Data/CleanupShoppingListDuplicates.php`
   - Uses `$moduleDataSetup->getTable('tr_shopping_list_item')`.
   - **Expected** and table-prefix-safe.

5. `README.md`
   - Documents current table naming.
   - **Expected**.

### References to prospective names

- `ostoya_shopping_list`: **no matches**.
- `ostoya_shopping_list_item`: **no matches**.

This confirms runtime schema/code is consistently aligned to legacy `tr_` names today.

### Related naming checks requested

- `list_name`: actively used in schema, services, importer, resolvers, controllers, templates (expected domain field).
- `setListName`: present in importer (`setCustomerId(...)->setListName(...)`).
- `getListName`: present in controller/templates.
- `setName(`: no matches.
- `getName`: not part of requested grep set, but GraphQL/product contexts use product name fields; no shopping-list field migration implied.
- `addFieldToFilter('name'` / `addFieldToFilter("name"`: no matches.

## 2) Naming options

### Option A: Keep legacy `tr_` table names

**Pros**
- Safest for existing production data.
- No table migration needed.
- Least deployment risk.

**Cons**
- Branding inconsistency with `Ostoya` namespace.
- Can be confusing for new maintainers.

### Option B: Rename to `ostoya_` table names

**Pros**
- Cleaner naming.
- Better alignment with module namespace.

**Cons**
- Requires data migration.
- `setup:upgrade` can be slower/riskier on large tables.
- Must use Magento declarative migration semantics (not raw/naive rename).
- Requires `db_schema_whitelist.json` updates where applicable.
- Requires backups and staging validation before production rollout.

## 3) Recommended approach

**Recommendation now: keep `tr_` table names.**

Given existing production compatibility concerns and no functional defect caused by the table prefix, retaining `tr_shopping_list` and `tr_shopping_list_item` is the lowest-risk approach.

If business/architecture strongly requires rebranding to `ostoya_`, do it in a **separate dedicated release/PR** with explicit migration validation, rollback strategy, and staging burn-in.

## 4) Safe migration plan (future, optional)

### Phase 1 (now, non-destructive)
- Keep existing `tr_` tables.
- Prefer table-name indirection patterns already used (`getTable(...)`) where practical.
- Optionally add centralized constants (if desired):
  - `TABLE_SHOPPING_LIST = 'tr_shopping_list'`
  - `TABLE_SHOPPING_LIST_ITEM = 'tr_shopping_list_item'`
- Keep README/comments explicit that `tr_` names are intentionally preserved for backward compatibility.

### Phase 2 (staging preparation)
- Create staging backup/snapshot.
- Baseline counts:
  - `SELECT COUNT(*) FROM tr_shopping_list;`
  - `SELECT COUNT(*) FROM tr_shopping_list_item;`
- Validate duplicates/constraints and run existing cleanup patch logic where appropriate.
- Dry-run `bin/magento setup:upgrade` on staging and time it.

### Phase 3 (declarative migration release)
- Introduce new tables using Magento declarative schema migration semantics, e.g.:
  - `ostoya_shopping_list` with `onCreate="migrateDataFromAnotherTable(tr_shopping_list)"`
  - `ostoya_shopping_list_item` with `onCreate="migrateDataFromAnotherTable(tr_shopping_list_item)"`
- Update resource models and collection joins to new table names.
- Update/generate `db_schema_whitelist.json` as required by deployment process.
- Validate commands:
  - `bin/magento setup:upgrade`
  - `bin/magento setup:di:compile`
  - `bin/magento setup:db:status`
  - `bin/magento cache:flush`

### Phase 4 (post-migration verification)
- Re-check row counts on new tables and compare with pre-migration baselines.
- Verify frontend, admin import flow, and GraphQL operations.
- Deploy only after parity validation succeeds.

## 5) Immediate code-change policy for this task

### Allowed
- Add comments/constants documenting intentional legacy `tr_` names.
- Update README clarifying backward compatibility.
- Replace duplicated hardcoded table strings with constants only when low-risk.

### Not allowed
- Do not rename actual tables in schema.
- Do not change `db_schema.xml` table names.
- Do not drop old tables.
- Do not add destructive migration behavior.

## Validation evidence (commands run)

```bash
grep -R "tr_shopping_list" . -n
grep -R "tr_shopping_list_item" . -n
grep -R "ostoya_shopping_list" . -n || true
grep -R "ostoya_shopping_list_item" . -n || true
grep -R "list_name" . -n
grep -R "setListName" . -n || true
grep -R "getListName" . -n || true
grep -R "setName(" . -n || true
grep -R "addFieldToFilter('name'" . -n || true
grep -R 'addFieldToFilter("name"' . -n || true
php -l Model/ResourceModel/ShoppingList.php
php -l Model/ResourceModel/ShoppingListItem.php
php -l Model/ResourceModel/ShoppingList/Collection.php
php -l Setup/Patch/Data/CleanupShoppingListDuplicates.php
```

`bin/magento` is not present in this repository root, so Magento CLI compile/status checks were not run in this environment.
