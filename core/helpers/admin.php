<?php
/**
 * Admin helper funtions
 *
 * This file is used to display general ui elements, and tools for use in wp-admin.
 *
 * @package Layers
 * @since Layers 1.1.0
 */

/**
 * Backs up builder pages as HTML
 */
if( !function_exists( 'layers_backup_builder_pages' ) ) {
	function layers_backup_builder_pages(){

		if( !check_ajax_referer( 'layers-backup-pages', 'layers_backup_pages_nonce', false ) ) die( 'You threw a Nonce exception' ); // Nonce

		if( !isset( $_POST[ 'pageid' ] ) ) wp_die( __( 'You shall not pass' , 'layerswp' ) );

		// Get the post data
		$page_id = $_POST[ 'pageid' ];
		$page = get_post( $page_id );

		// Start the output buffer
		ob_start();
		dynamic_sidebar( 'obox-layers-builder-' . $page->ID );

		$page_content = trim( ob_get_clean() );
		$page_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $page_content);
		$page_content = strip_tags( $page_content , '<p><b><i><strong><em><quote><a><h1><h2><h3><h4><h5><img><script>' );
		$page_content = $page_content;

		$page_meta_key = 'layers_page_content_' . date( 'YmdHi' );

		update_post_meta( $page_id , $page_meta_key, $page_content );

		// Flush the output buffer
		ob_flush();
	}
} // layers_builder_page_backup
add_action( 'wp_ajax_layers_backup_builder_pages', 'layers_backup_builder_pages' );
/**
 *  The following function creates a builder page
 *
 * @param string Page Title (optional)
 * @return array Page ID
 */
if( !function_exists( 'layers_create_builder_page' ) ) {
	function layers_create_builder_page( $page_title = 'Builder Page', $page_id = NULL ) {

		$page['post_type'] = 'page';
		$page['post_status'] = 'publish';
		$page['post_title'] = $page_title;

		if( NULL != $page_id ) {
			$page['ID'] = $page_id;
			$pageid = wp_update_post ($page);
		} else {
			$pageid = wp_insert_post ($page);
		}
		if ( 0 != $pageid ) {
			update_post_meta( $pageid , '_wp_page_template', LAYERS_BUILDER_TEMPLATE );
		}

		return $pageid;
	}
}

/**
 * Get all builder pages and store in global variable
 *
 * @return  object    $layers_builder_pages wp_query list of builder pages.
*/

if( ! function_exists( 'layers_get_builder_pages' ) ) {
	function layers_get_builder_pages () {
		global $layers_builder_pages;

		// Fetch Builder Pages
		$layers_builder_pages = get_posts(array(
			'post_status' => 'publish,draft,private',
			'post_type' => 'page',
			'meta_key' => '_wp_page_template',
			'meta_value' => LAYERS_BUILDER_TEMPLATE,
			'posts_per_page' => -1
		));

		return $layers_builder_pages;
	}
}

/**
 * Conditional check if is Layers page
 *
 * @param   int   $post_id   (Optional) ID of post to check. Uses global $post ID if none provided.
 */

if( ! function_exists( 'layers_is_builder_page' ) ) {
	function layers_is_builder_page( $post_id = false ){
		global $post;

		// Be sure to set a post id for use
		if ( !$post_id && isset( $post ) && isset( $post->ID ) ) {
			$post_id = $post->ID;
		}

		// If there is a post_id, check for the builder page
		if ( isset( $post_id ) ) {
			if( LAYERS_BUILDER_TEMPLATE == get_post_meta( $post_id, '_wp_page_template', true ) ) {
				return true;
			}
		}

		// Fallback
		return false;
	}
}

/**
 * Filter Layers Pages in wp-admin Pages
 *
 * @TODO: think about moving this function to it own helpers/admin.php,
 * especially if more work is to be done on admin list.
 */

if ( ! function_exists( 'layers_filter_admin_pages' ) ) {
	function layers_filter_admin_pages() {
		global $typenow;

		if ( 'page' == $typenow && isset( $_GET['filter'] ) && 'layers' == $_GET['filter'] ) {
			set_query_var(
				'meta_query',
				array(
					'relation' => 'AND',
					array(
						'key' => '_wp_page_template',
						'value' => LAYERS_BUILDER_TEMPLATE,
					)
				)
			);
		}
	}
}
add_filter( 'pre_get_posts', 'layers_filter_admin_pages' );

/**
 * Change views links on wp-list-table - all, published, draft, etc - to maintain layers page filtering
 * TODO: some kind of feeback so user knows he is in the Layers filter - maybe h2 to "Layers Pages"
 */

if ( ! function_exists( 'layers_filter_admin_pages_views' ) ) {
	function layers_filter_admin_pages_views( $views ) {
		global $typenow;

		if ( 'page' == $typenow && isset( $_GET['filter'] ) && 'layers' == $_GET['filter'] ) {
			foreach ($views as $view_key => $view_value ) {
				$query_arg = '&filter=layers';
				$view_value = preg_replace('/href=\'(http:\/\/[^\/"]+\/?)?([^"]*)\'/', "href='\\2$query_arg'", $view_value);
				$views[$view_key] = $view_value;
			}
		}
		return $views;
	}
}
//add_filter( "views_edit-page", 'layers_filter_admin_pages_views' );

/**
 * Add builder edit button to the admin bar
 *
 * @return null Nothing is returned, the Edit button is added the admin toolbar
*/

if( ! function_exists( 'layers_edit_layout_admin_menu' ) ) {
	function layers_edit_layout_admin_menu(){
		global $wp_admin_bar, $post;

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$args = array(
			'id'    => 'layers-edit-layout',
			'title' => '<span class="ab-icon"></span><span class="ab-label">' . __( 'Edit Layout' , 'layerswp' ) . '</span>',
			'href'  => add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() ),
			'meta'  => array( 'class' => 'my-toolbar-page' )
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'layers_edit_layout_admin_menu', 90 );

/**
 * Add "Add New Layers Page" to the admin bar
 *
 * @return null Nothing is returned, the new button is added the admin toolbar
*/

if( ! function_exists( 'layers_add_new_page_admin_menu' ) ) {
	function layers_add_new_page_admin_menu(){
		global $wp_admin_bar, $post;

		$args = array(
			'parent' => 'new-content',
			'id'    => 'layers-add-page',
			'title' =>__( 'Layers Page' , 'layerswp' ),
			'href' => admin_url( 'admin.php?page=layers-add-new-page' )
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'layers_add_new_page_admin_menu', 90 );

// Output custom css to add Icon to admin bar edit button.
if( ! function_exists( 'layers_add_builder_edit_button_css' ) ) {
	function layers_add_builder_edit_button_css() {
		global $pagenow;
		if ( 'post.php' === $pagenow || ! is_admin() ) : ?>
			<style>
			#wp-admin-bar-layers-edit-layout .ab-icon:before{
				font-family: "layers-interface" !important;
				content: "\e62f" !important;
				font-size: 16px !important;
			}
			</style>
		<?php endif;
	}
}
add_action('wp_print_styles', 'layers_add_builder_edit_button_css');
add_action('admin_print_styles-post.php', 'layers_add_builder_edit_button_css');

/**
 * Render the dropdown of builder pages in Customizer interface.
 */

if( !function_exists( 'render_builder_page_dropdown' ) ) {
	function render_builder_page_dropdown() {
		
		global $wp_customize;

		if( !$wp_customize ) return;

		//Get builder pages.
		$layers_pages = layers_get_builder_pages();

		// Create builder pages dropdown.
		if( $layers_pages ){
			ob_start(); ?>
			<div class="layers-customizer-pages-dropdown">
				<select>
					<option value="init"><?php _e( 'Builder Pages:' , 'layerswp' ) ?></option>
					<?php foreach( $layers_pages as $page ) { ?>
						<?php // Page URL
						$edit_page_url = get_permalink( $page->ID ); ?>
						<option value="<?php echo esc_attr( $edit_page_url ); ?>"><?php echo $page->post_title ?></option>
					<?php } ?>
				</select>
			</div>
			<?php
			// Get the Drop Down HTML
			$drop_down = ob_get_clean();

			// Return the Drop Down
			return $drop_down;
		}
	}
}
