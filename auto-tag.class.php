<?php

/*
Part of the auto-tag plugin for wordpress

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

class AutoTag {

    protected $rmtags;
    protected $addtags;

    public static function forbidden_tag($forbidden,$tag)
    {
		if (is_array($forbidden) && !empty($forbidden)){
			foreach($forbidden as $forbid){
				if ($forbid !='')
					if(strpos(strtolower($tag), strtolower($forbid))!==false)
						return true;
			}
		}
		return false;
	}

	function auto_tag_yahoo($content, $num, $remove_tags)
    {
		$senddata = http_build_query(array(
            'context'=>urlencode(utf8_decode(strip_tags($content))),
            'output'=>'json',
            'appid'=>'BR_m.GrV34HyixkLbaEHmgSInktZjX1AohGCN6F6ywe5ojN01XGwDw4eRrV3rFdY8zdrhNWH'
		));
        $ret = array();
        try {
		    $data = wp_remote_post('http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction', array('body' => $senddata));
            if(!is_array($data)){
                throw new ErrorException($data->get_error_message());
            }
            if($json=json_decode($data['body'])){
                $i=0;
                $kws = $json->ResultSet->Result;
                shuffle($kws);
                foreach($kws as $kw) {
                    if ($i>=$num) break;
                    if (!AutoTag::forbidden_tag($remove_tags, $kw) && strlen($kw) > 3) {
                        $ret[].= $kw;
                        $i++;
                    }
                }
            }
        } catch (ErrorException $e) {


        }

		return join(',', $ret);
	}


	public function __construct()
    {
		add_action('save_post', array($this, 'tag_posts'), 1);
	}

	public static function trim_tags(&$item, $k)
    {
		$item = trim($item);
	}

    public function remove_tags($tag)
    {
        if(in_array($tag, explode(',', $this->rmtags))) {
            return false;
        }
        return true;
    }

	public function tag_posts($postid)
    {
        $removed_tags = @filter_input(INPUT_POST, 'auto_tag_removed_tags', FILTER_SANITIZE_STRING);
        $removed_tag = @filter_input(INPUT_POST, 'auto_tag_removed_tag', FILTER_SANITIZE_STRING);
        $added_tags = @filter_var( $_POST['tax_input']['post_tag'], FILTER_SANITIZE_STRING);
        $added_tag = @filter_var($_POST['new_tag']['post_tag'], FILTER_SANITIZE_STRING);
        $disable = @filter_var($_POST['autotag_disabled_on_post'], FILTER_SANITIZE_NUMBER_INT);

        $this->rmtags = $removed_tags . ($removed_tag ? ',' . $removed_tag : '');
        $this->addtags = $added_tags . ($added_tag ? ',' . $added_tag : '');

        $removed = explode(',', strtolower(get_option('autotag_remove_tags')) .
        ($this->rmtags ? ',' . $this->rmtags : ''));

        update_post_meta($postid, '_auto_tag_removed_tags', $this->rmtags);
        update_post_meta($postid, '_auto_tag_disabled', $disable);

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        $post = get_post($postid, ARRAY_A);

        if ( 'page' == $post['post_type'] ) {
            if (!current_user_can( 'edit_page', $postid ) || !get_option('autotag_tag_pages'))
                return;
        }else{
            if (!current_user_can( 'edit_post', $postid ) || !get_option('autotag_tag_posts'))
                return;
        }

        $tags = array_merge(
            wp_get_post_tags($postid, array( 'fields' => 'name' )),
            explode(',', $this->addtags)
        );

        $tags = array_filter($tags, array($this, 'remove_tags'));
        $old_tags = join(',', $tags);
        $keywords = $old_tags;
        if(!$disable) {
            $yahoo_num = (get_option('autotag_number') ? get_option('autotag_number') : 5);
            array_walk($removed, 'AutoTag::trim_tags');

            $content = $post['post_title']." ".$post['post_content'];

            $num_tags_to_add = $yahoo_num - count($tags);
            $old_tags = join(',', $tags);
            $keywords = AutoTag::auto_tag_yahoo($content, $num_tags_to_add, $removed).($old_tags ? ','.$old_tags : '');
        }

        remove_action('save_post', array($this, 'tag_posts'));

        wp_set_post_tags( $postid, $keywords, false);
	}
	
}
