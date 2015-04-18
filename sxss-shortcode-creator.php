<?php
/*
Plugin Name: sxss Shortcode Creator
Plugin URI: http://sxss.nw.am
Description: Create reusable content and use a shortcode to insert it in posts, pages and widgets
Author: sxss
Version: 0.1.1
Author URI: http://sxss.nw.am
*/

// Register Custom Post Type
function sxss_sc_post_type() {

	$labels = array(
		'name'                => _x( 'Shortcodes', 'Post Type General Name', 'sxss_sc' ),
		'singular_name'       => _x( 'Shortcode', 'Post Type Singular Name', 'sxss_sc' ),
		'menu_name'           => __( 'Shortcodes', 'sxss_sc' ),
		'parent_item_colon'   => __( 'Parent Item:', 'sxss_sc' ),
		'all_items'           => __( 'All Shortcodes', 'sxss_sc' ),
		'view_item'           => __( 'View Shortcode', 'sxss_sc' ),
		'add_new_item'        => __( 'Add New Shortcode', 'sxss_sc' ),
		'add_new'             => __( 'Add New', 'sxss_sc' ),
		'edit_item'           => __( 'Edit Shortcode', 'sxss_sc' ),
		'update_item'         => __( 'Update Shortcode', 'sxss_sc' ),
		'search_items'        => __( 'Search Shortcodes', 'sxss_sc' ),
		'not_found'           => __( 'Not found', 'sxss_sc' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'sxss_sc' ),
	);
	$args = array(
		'label'               => __( 'sxss_shortcodes', 'sxss_sc' ),
		'description'         => __( 'Custom [shortcodes] to include in posts and pages', 'sxss_sc' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'revisions' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_icon'           => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'rewrite'             => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'sxss_shortcodes', $args );

}

// Hook into the 'init' action
add_action( 'init', 'sxss_sc_post_type', 0 );

// Add Shortcode
function sxss_sc_shortcode( $atts ) {

	// Attributes
	extract( shortcode_atts(
		array(
			'id' => false
		), $atts )
	);

	// If the shortcode has an id attribute
	if( false != $id ) {
		// Get shortcode data by id
		$post = get_post( $id, ARRAY_A );
		// If id was a valid shortcode-id
		if( NULL != $post )
			return do_shortcode( $post['post_content'] );
	}
}

add_shortcode( 'content', 'sxss_sc_shortcode' );

// Sidebox to copy the shortcode
function sxss_sc_register_meta_box() 
{
	add_meta_box('sxss_sc_meta_box', __('Copy & Paste' , 'sxss_sc'), 'sxss_sc_meta_box', 'sxss_shortcodes', 'side', 'high');
	add_meta_box('sxss_sc_meta_box_link', __('More Plugins & Themes' , 'sxss_sc'), 'sxss_sc_meta_box_link', 'sxss_shortcodes', 'side', 'low');
	add_meta_box('sxss_sc_meta_box_shortcodes', __('Shortcodes' , 'sxss_sc'), 'sxss_sc_meta_box_shortcodes', 'post', 'normal', 'high');
	add_meta_box('sxss_sc_meta_box_shortcodes', __('Shortcodes' , 'sxss_sc'), 'sxss_sc_meta_box_shortcodes', 'page', 'normal', 'high');
}

add_action('admin_menu', 'sxss_sc_register_meta_box');

// Sidebox content (shortcode)
function sxss_sc_meta_box() {
	$post_id = get_the_ID();
	echo  "<p>" . __('Copy and paste this shortcode in your post, right where the content should appear.' , 'sxss_sc') . "</p>";
	echo '<p>[content id="' . $post_id . '"]</p>';
}

// Sidebox content (link)
function sxss_sc_meta_box_link() {
	echo '<p align="center"><a target="_blank" title="sxss Plugins & Themes" href="http://sxss.nw.am"><img src="' . plugins_url( 'sxss-plugins.png' , __FILE__ ) . '"></a></p>';
}

function sxss_sc_meta_box_shortcodes() {

	?>
		<script>

		jQuery( document ).ready(function() {

			// WP Editor
			var wpEditor = jQuery('textarea.wp-editor-area');

			// Start and endposition of the current selection / cursor position
			var startSel, endSel;

			// Save sursor position
			wpEditor.bind('focusout', function() {
			    startSel = this.selectionStart;
			    endSel = this.selectionEnd;
			});

			// Post shortcode to the WP Editor
			jQuery( ".sxss_add_shortcode" ).on( "click", function() {

				// Get shortcode from the clicked element
				var shortcode = jQuery( this ).data('shortcode');

				// If editor is in 'visual' mode
				if( jQuery("#wp-content-wrap").hasClass("tmce-active") ) {
					// insert shortcode into TinyMCE
					tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
			    } 
			    // If editor is in 'text' mode
			    else {
					// Check if Editor is empty
			    	if( startSel || startSel == '0') {
						// Insert shortcode at the current 
						wpEditor.val( wpEditor.val().substring(0, startSel) + shortcode + wpEditor.val().substring(endSel, wpEditor.val().length) );
					} 
					else {
							// Get textarea content
							var content = wpEditor.val();
							// Add shortcode to content
							wpEditor.val( content + shortcode );
					}
			    }
			});

		});

		</script>
	<?php

	// Query for custom shortcodes
	$the_query = new WP_Query( 'post_type=sxss_shortcodes&orderby=date&order=DESC' );

	// If there are custom shortcodes
	if ( $the_query->have_posts() ) : 

		// Add a button for each shortcode (to insert the shortcode in the WP editor)
		while ( $the_query->have_posts() ) : $the_query->the_post();
		
			echo '<span class="sxss_add_shortcode button" data-shortcode=\'[content id="' . get_the_ID() . '"]\'>' . get_the_title() . ' <span style="color: rgba( 0, 0, 0, 0.3 );">(' . get_the_ID() . ')</span></span> ';

		endwhile;

		// Reset custom query
		wp_reset_postdata();

	else :
		echo '<a class="button" href="post-new.php?post_type=sxss_shortcodes">' . __('Create a custom shortcode' , 'sxss_sc') . '</a>';
	endif;

	echo '</ul>';
}