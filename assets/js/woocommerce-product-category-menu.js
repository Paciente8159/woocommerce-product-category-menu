"use strict";

jQuery(document).ready(function ($) {
  $(".product-category-menu-item").click(function () {
    $(this).toggleClass("product-category-menu-item-collapsed");
  });

  $(".product-category-menu-item-control>a").click(
    function (e) {
      e.stopPropagation();
    }
  );

  $(".product-category-menu-item-control").click(function (e) {
    if(!$(this).find(".product-category-submenu-toogle").length) {
      e.stopPropagation();
      $(this).find("a").get(0).click();
    }
  });
});
