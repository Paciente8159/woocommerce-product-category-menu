# woocommerce-product-category-menu
Adds a Woocommerce automatic category and subcategory product menu (fully nested) for woocommerce via shortcode.
Also generates a menu that is auto updated.

# Customization
## Available filters for shorcode menu
The menu collapse icon can be modified via ```jcem_wc_toggle_menu_control``` hook

### Shortcode usage

Just call the shortcode

```
[product_category_menu (optional attributes)]
```

| Attribute name | Value |
| --- | --- |
| hide_empty | "0"(default) or "1" - hides empty (no products) categories/subcategories |
| parent_id | "0"(default to no parent) or category ID - builds a menu from top or a sub menu starting from the category ID |
| show_count | "0"(default) or "1" - adds the product count to the end of the category name. Can be modified via jcem_wc_show_product_count hook |


## Available filters for WP menu
By default a menu named ```jcem_wc_product_category_menu``` is created.
To generate other menus given any other condition (for example other language or custom taxonomy filtering or show empty items) use these hooks

| Hook name | Value |
| --- | --- |
| jcem_wc_product_category_menu_init_name | Hook to append a name to the jcem_wc_product_category_menu title |
| jcem_wc_product_category_menu_init_args | Hook to add custom taxonomy filtering to the product categories query |
