<?php

/*
Plugin Name: Auto Tags
Plugin URI: http://wordpress.org/extend/plugins/auto-tag/
Description: Tag posts on save and update from tagthe.net and yahoo services.
Version: 0.5.1
Author: Jonathan Foucher
Author URI: http://jfoucher.com

Copyright 2009-2012 Jonathan Foucher

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


require_once('auto-tag.class.php');
require_once('options-form.class.php');

require_once('auto-tag-setup.class.php');
require_once('auto-tag-meta-box.class.php');

$auto_tag_setup=new AutoTagSetup();
$auto_tag=new AutoTag();

load_plugin_textdomain ('auto-tag', FALSE, dirname (plugin_basename(__FILE__)) . '/i18n');
if ( is_admin() ){
    add_action( 'load-post.php', 'call_AutoTagMetaBox' );
}

function call_AutoTagMetaBox()
{
    return new AutoTagMetaBox();
}

function autotag_settings_link( $links, $file )
{
    if ( $file != plugin_basename( __FILE__ ))
        return $links;

    $settings_link = '<a href="admin.php?page=auto_tag_options_top_menu">' . __( 'Settings', 'auto-tag' ) . '</a>';

    array_unshift( $links, $settings_link );

    return $links;
}


add_filter( 'plugin_action_links', 'autotag_settings_link', 10, 2);
