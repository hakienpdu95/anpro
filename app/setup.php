<?php

/**
 * Theme bootstrap — loads helpers then the three Boot modules in order.
 *
 * Boot/ThemeSupport.php  — WP theme support, menus, image sizes, sidebars, editor
 * Boot/MetaData.php      — custom meta tables, CPT/Taxonomy registration, Meta Box
 * Boot/Services.php      — optimizations, cache, search, queries, AJAX
 */

namespace App;

require_once get_theme_file_path('app/helpers.php');
require_once get_theme_file_path('app/Boot/ThemeSupport.php');
require_once get_theme_file_path('app/Boot/MetaData.php');
require_once get_theme_file_path('app/Boot/Services.php');
