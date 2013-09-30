<?php

/*
 *
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

class AutoTagSetup
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'display_menu'));
    }



    public function display_menu()
    {
        add_menu_page(
            __('Manage your auto tag options', 'auto-tag'),
            __('Auto Tags', 'auto-tag'),
            'manage_options',
            'auto_tag_options_top_menu',
            array($this, 'show_settings_menu'),
            plugin_dir_url( __FILE__ ).'/images/auto_tags_sm.png',
            102
        );
        if (function_exists('add_submenu_page')) {

            add_submenu_page(
                'auto_tag_options_top_menu',
                __('Auto Tag Options', 'auto-tag'),
                __("Settings", 'auto-tag'),
                'manage_options',
                'auto_tag_options_top_menu',
                array($this, 'show_settings_menu')
            );

            add_submenu_page(
                'auto_tag_options_top_menu',
                __('Auto Tag Tools', 'auto-tag'),
                __("Tools", 'auto-tag'),
                'manage_options',
                'auto_tag_options_tools_menu',
                array($this, 'show_tools_menu')
            );
        }

    }




    public function show_tools_menu()
    {

        echo '<div class="wrap">
<div class="icon32"><img src="'.plugin_dir_url( __FILE__ ).'/images/auto_tags.png'.'" /></div><h2>';
        echo __('Auto Tag Tools','auto-tag');
        echo'</h2>';
        echo '<div class="postbox-container" style="width:70%">';

        echo '<form method="post" class="tools" action="admin.php?page=auto_tag_options_tools_menu&action=tools">';
        echo '<fieldset>
        <h3>
        Retag
        </h3>';

        if(!empty($_POST)){
            $this->tools_handler();
        }

        submit_button(__('Retag all posts', 'auto-tag'), 'primary', 'submit' , false);
        echo ' ';
        submit_button(__('Retag all pages', 'auto-tag'), 'primary', 'submit' , false);
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';
        echo $this->helpBox();
        echo '</div>';
    }


    public function show_settings_menu()
    {
        if(!empty($_POST)){
            $this->save_settings();
        }
        echo '<div class="wrap">
<div class="icon32"><img src="'.plugin_dir_url( __FILE__ ).'/images/auto_tags.png'.'" /></div><h2>';
        echo __('Auto Tags Settings','auto-tag');
        echo'</h2>';
        echo '<div class="postbox-container" style="width:70%">';


        $form = new OptionsForm('admin.php?page=auto_tag_options_top_menu&action=save_options');


        $generalOptions[] = new OptionsFormOption('autotag_tag_posts', __('Tag posts', 'auto-tag'), 'checkbox', get_option('autotag_tag_posts'), __('Enable automatic tagging of your posts by <b>WP Auto Tag</b>', 'auto-tag'));
        $generalOptions[] = new OptionsFormOption('autotag_tag_pages', __('Tag pages', 'auto-tag'), 'checkbox', get_option('autotag_tag_pages'), __('Enable automatic tagging of your pages by <b>WP Auto Tag</b>', 'auto-tag'));
        $generalOptions[] = new OptionsFormOption('autotag_number', __('Maximum number of tags per post', 'auto-tag'), 'text', get_option('autotag_number'));
        $generalOptions[] = new OptionsFormOption('autotag_remove_tags', __('Remove these tags (comma separated)', 'auto-tag'), 'text', get_option('autotag_remove_tags'), __('Append new tags to existing tags?', 'auto-tag'));

        $generalFieldset = new OptionsFormFieldset(
            __('General Settings', 'auto-tag'),
            $generalOptions,
            __('Enable or disable the automatic tagging of your posts through WP Auto Tag', 'auto-tag')
        );

        $form->addFieldset($generalFieldset);

        $form->display();

        echo '</div>';
        echo $this->helpBox();
        echo '</div>';
    }

    public function helpBox()
    {
        return '<div class="postbox-container updated" style="width:25%;">
        <h3>Help me work on this plugin!</h3>
        If you like this plugin, please consider:
         <ul style="list-style: disc; margin-left:15px">
         <li><a href="http://wordpress.org/extend/plugins/auto-tag/stats/">Giving it a 5 star rating on wordpress.org</a></li>
         <li>Linking to it or blogging about it</li>
         <li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YBXXG4SGN2C4J">Making a small donation to further development</a></li>
         </ul>

        </div>';
    }



    public function save_settings()
    {
        $fields['autotag_tag_posts'] = (isset($_POST['autotag_tag_posts']) ? 1 : 0);
        $fields['autotag_tag_pages'] = (isset($_POST['autotag_tag_pages']) ? 1 : 0);

        $fields['autotag_remove_tags'] = strip_tags(filter_var($_POST ['autotag_remove_tags'], FILTER_SANITIZE_STRING));
        $fields['autotag_number'] = strip_tags(filter_var($_POST ['autotag_number'], FILTER_SANITIZE_NUMBER_INT));

        $errors = array();

        if (! count ($errors)) {
            update_option('autotag_tag_posts', $fields['autotag_tag_posts']);
            update_option('autotag_tag_pages', $fields['autotag_tag_pages']);
            update_option('autotag_remove_tags', $fields ['autotag_remove_tags']);
            update_option('autotag_number', $fields ['autotag_number']);

            $this->custom_notice('updated', __('Your settings have been saved successfully', 'auto-tag'));

        }else{
            $this->custom_notice('error', __('There is an error with your settings. Please correct and try again', 'auto-tag'));
        }
    }

    public function tools_handler()
    {
        $posts= array();
        if ($_POST['submit'] == __('Retag all posts', 'auto-tag')) {
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
            );

            $posts = get_posts($args);

        }elseif ($_POST['submit'] == __('Retag all pages', 'auto-tag')) {
            $args = array(
                'post_type' => 'page',
                'post_status' => 'publish',
            );

            $posts = get_posts($args);
        }

        echo '<ul>';

        foreach($posts as $p) {
            $post = (array) $p;
            $post['post_ID'] = $p->ID;
            $post['action'] = 'save';
            edit_post($post);

            echo '<li>'.sprintf(__('Post <b>%s</b> re-tagged', 'auto-tag'), $p->post_title).'</li>';

            flush();

        }
        echo '</ul>';

    }

    public static function custom_notice($type, $message)
    {
        echo '<div class="'.$type.'"><p>'.$message.'</p></div>';
    }




}