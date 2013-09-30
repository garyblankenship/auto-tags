<?php

class AutoTagMetaBox
{
    const LANG = 'auto-tag';

    public function __construct()
    {
        add_action( 'add_meta_boxes', array( &$this, 'add_auto_tag_meta_box' ) );
    }

    public function add_auto_tag_meta_box()
    {
        if(get_option('autotag_tag_posts')){
            add_meta_box(
                'auto_tag_removed_tags'
                ,__( 'Never use these tags', self::LANG )
                ,array( &$this, 'render_meta_box_content' )
                ,'post'
                ,'side'
                ,'high'
            );
        }
        if(get_option('autotag_tag_pages')){
            add_meta_box(
                'auto_tag_removed_tags'
                ,__( 'Never use these tags', self::LANG )
                ,array( &$this, 'render_meta_box_content' )
                ,'page'
                ,'side'
                ,'high'
            );
        }
    }

    public function render_meta_box_content($post)
    {
        $meta = get_post_meta($post->ID);

        $rmt = (isset($meta['_auto_tag_removed_tags']) ? $meta['_auto_tag_removed_tags'][0] : '');
        $disabled = (isset($meta['_auto_tag_disabled']) && $meta['_auto_tag_disabled'][0] ? 'checked="checked"' : '');

        echo '<div class="inside">
<div class="tagsdiv" id="post_tag">
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
	<p>Add or remove tags</p>
	<textarea name="auto_tag_removed_tags" rows="3" cols="20" class="the-tags" id="tax-input-post_tag">'.$rmt.'</textarea></div>
 		<div class="ajaxtag hide-if-no-js">
		<label class="screen-reader-text" for="new-tag-post_tag">Tags</label>
		<div class="taghint" style="visibility: hidden; ">Add New Tag</div>
		<p><input type="text" id="new-tag-post_tag" name="auto_tag_removed_tag" class="newtag form-input-tip" size="16" autocomplete="off" value="">
		<input type="button" class="button tagadd" value="Add" tabindex="3"></p>
	</div>
	<p class="howto">Separate tags with commas</p>
		</div>
	<div class="tagchecklist"></div>
</div>
<p><label><input type="checkbox" id="autotag_disabled_on_post" name="autotag_disabled_on_post" value="1"'.$disabled.'> '.__('Disable further tagging on this post', 'auto-tag').'</label></p>
</div>';
    }
}