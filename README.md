# woocommerce-product-category-menu
Adds a Woocommerce automatic category and subcategory product menu (fully nested) for woocommerce via shortcode.
Also generates a menu that is auto updated.

# Customization
## Available filters
The menu collapse icon can be modified via ´jcem_wc_toggle_menu_control´ hook

## Usage

Just call the shortcode

´´´
[product_category_menu (optional attributes)]
´´´

| Attribute name | Value |
| --- | --- |
| hide_empty | "0"(default) or "1" - hides empty (no products) categories/subcategories |
| parent_id | "0"(default to no parent) or category ID - builds a menu from top or a sub menu starting from the category ID |
| show_count | "0"(default) or "1" - adds the product count to the end of the category name. Can be modified via jcem_wc_show_product_count hook |
