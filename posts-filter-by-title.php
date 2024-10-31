<?php
/*
Plugin Name: Posts Filter by Title
Plugin URI: http://wordpress.org/extend/plugins/posts-filter-by-title/
Description: This is a simple plugin which adds another drop-down box of Post Titles into the filter section on posts listing page in the wordpress backend. This is usefull when we have more then 99 posts in the site and want to choose a particular post without having to remember and search by post title or navigate through the pagination.
Version: 0.1
Author: Subharanjan
Author URI: http://www.subharanjan.in/about-subharanjan/
Author Email: subharanjanmantri@gmail.com
License:

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 
*/

class PostsFilterbyTitle {

	/**
	 * Constructor
	 */
	function __construct() {
		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_posts_filter_by_title' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_posts_filter_by_title() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_posts_filter_by_title() {
		if ( is_admin() ) {
			add_filter( 'parse_query', array( &$this, 'pfbt_filter_posts_by_title' ) ); 
			add_action( 'restrict_manage_posts', array( &$this, 'pfbt_create_filter_selection_box' ) );
		}
	}

	/**
	 * Creates an extra dropdown box with all the posts' title for filteration
	 * @return void simply adds another select box
	 */	
	function pfbt_create_filter_selection_box(){
		$type = 'post';
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		}

		//only add filter to post type "post"
		if ('post' == $type){
			
			/* remove the function for the filter 'parse_query' */
			remove_filter( 'parse_query', array( &$this, 'pfbt_filter_posts_by_title' ) ); 
			
			$my_query = null;
			$values = array();			
			$args = array(
				'post_type' 	=> 'post',
				'posts_per_page'=> -1,
				'orderby'		=> 'title',
				'order'			=> 'ASC'
			);
			$my_query = new WP_Query($args);
			if( $my_query->have_posts() ) {
				while ($my_query->have_posts()) : $my_query->the_post();
					$post_ID 	= $my_query->post->ID;
					$post_title = $my_query->post->post_title;
					$post_slug 	= $my_query->post->post_name;
					$values[$post_title] = $post_ID;
				endwhile;
			}	
			wp_reset_query();
			wp_reset_postdata();
			?>
			<select name="posts_title_filter" id="posts_title_filter">
				<option value=""><?php _e('View all Posts', 'pfbt'); ?></option>
				<?php
				$current_v = isset($_GET['posts_title_filter'])? $_GET['posts_title_filter']:'';
				foreach ($values as $label => $value) {
					printf(
						'<option value="%s"%s>%s</option>',
						$value,
						$value == $current_v? ' selected="selected"':'',
						$label
					);
				}
				?>
			</select>
			<?php
		}
	}


	/**
	 * if submitted filter by post id
	 * @param  (wp_query object) $query
	 * @return void
	 */
	function pfbt_filter_posts_by_title( $query ){
		global $pagenow;
		$type = 'post';
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		}
		if ( 'post' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['posts_title_filter']) && $_GET['posts_title_filter'] != '') {
			$postin  = array();
			$postin[] = $_GET['posts_title_filter'];
			$query->query_vars['post__in'] = $postin;
		}
	}  
} // end class
new PostsFilterbyTitle();

?>