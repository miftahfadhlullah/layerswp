<?php  /**
 * Post helper funtions
 *
 * This file is used to display post elements, from meta to media, to galleries, to in-post pagination, all post-related functions sit in this file.
 *
 * @package Layers
 * @since Layers 1.0.0
 */


/**
* Print post meta HTML
*
* @param    string         $post_id        ID of the post to use
* @param    array           $display        Configuration arguments. (date, author, categories, tags)
* @param    string         $wrapper        Type of html wrapper
* @param    string         $wrapper_class  Class of HTML wrapper
* @echo     string                          Post Meta HTML
*/

if( !function_exists( 'layers_post_meta' ) ) {
	function layers_post_meta( $post_id = NULL , $display = NULL, $wrapper = 'footer', $wrapper_class = 'meta-info' ) {
		// If there is no post ID specified, use the current post, does not affect post author, yet.
		if( NULL == $post_id ) {
			global $post;
			$post_id = $post->ID;
		}

		// If there are no items to display, return nothing
		if( !is_array( $display ) ) $display = array( 'date', 'author', 'categories', 'tags' );

		foreach ( $display as $meta ) {
			switch ( $meta ) {
				case 'date' :
					$meta_to_display[] = '<span class="meta-item meta-date"><i class="l-clock-o"></i> ' . get_the_time(  get_option( 'date_format' ) , $post_id ) . '</span>';
					break;
				case 'author' :
					$meta_to_display[] = '<span class="meta-item meta-author"><i class="l-user"></i> ' . layers_get_the_author( $post_id ) . '</span>';
					break;
				case 'categories' :
					$categories = '';

					// Use different terms for different post types
					if( 'post' == get_post_type( $post_id ) ){
						$the_categories = get_the_category( $post_id );
					} elseif( 'portfolio' == get_post_type( $post_id ) ) {
						$the_categories = get_the_terms( $post_id , 'portfolio-category' );
					} else {
						$the_categories = FALSE;
					}

					// If there are no categories, skip to the next case
					if( !$the_categories ) continue;

					foreach ( $the_categories as $category ){
						$categories[] = ' <a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s", LAYERS_THEME_SLUG ), $category->name ) ) . '">'.$category->name.'</a>';
					}
					$meta_to_display[] = '<span class="meta-item meta-category"><i class="l-folder-open-o"></i> ' . implode( __( ', ' , 'layerswp' ), $categories ) . '</span>';
					break;
				case 'tags' :
					$tags = '';

					if( 'post' == get_post_type( $post_id ) ){
						$the_tags = get_the_tags( $post_id );
					} elseif( 'portfolio' == get_post_type( $post_id ) ) {
						$the_tags = get_the_terms( $post_id , 'portfolio-tag' );
					} else {
						$the_tags = FALSE;
					}

					// If there are no tags, skip to the next case
					if( !$the_tags ) continue;

					foreach ( $the_tags as $tag ){
						$tags[] = ' <a href="'.get_category_link( $tag->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts tagged %s", LAYERS_THEME_SLUG ), $tag->name ) ) . '">'.$tag->name.'</a>';
					}
					$meta_to_display[] = '<span class="meta-item meta-tags"><i class="l-tags"></i> ' . implode( __( ', ' , 'layerswp' ), $tags ) . '</span>';
					break;
				break;
			} // switch meta
		} // foreach $display

		if( !empty( $meta_to_display ) ) {
			echo '<' . $wrapper . ( ( '' != $wrapper_class ) ? ' class="' . $wrapper_class .'"' : NULL ) . '>';
				echo '<p>';
					echo implode( ' ' , $meta_to_display );
				echo '</p>';
			echo '</' . $wrapper . '>';
		}
	}
} // layers_post_meta

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
if ( ! function_exists( 'layers_get_the_author' ) ) {
	function layers_get_the_author() {
		return sprintf( __( '<a href="%1$s" title="%2$s" rel="author">%3$s</a>' , 'layerswp' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'layerswp' ), get_the_author() ) ),
			esc_attr( get_the_author() )
		);
	}
} // layers_get_the_author


/**
 * Prints Comment HTML
 *
 * @param    object          $comment        Comment objext
 * @param    array           $args           Configuration arguments.
 * @param    int             $depth          Current depth of comment, for example 2 for a reply
 * @echo     string                          Comment HTML
 */
if( !function_exists( 'layers_comment' ) ) {
	function layers_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;?>
		<?php if( 2  < $depth && isset( $GLOBALS['lastdepth'] ) && $depth != $GLOBALS['lastdepth'] ) { ?>
			<div class="row comments-nested push-top">
		<?php } ?>
		<div <?php comment_class( 'content push-bottom well' ); ?> id="comment-<?php comment_ID(); ?>">
			<div class="avatar push-bottom clearfix">
				<?php edit_comment_link(__('(Edit)' , 'layerswp' ),'<small class="pull-right">','</small>') ?>
				<a class="avatar-image" href="">
					<?php echo get_avatar($comment, $size = '70'); ?>
				</a>
				<div class="avatar-body">
					<h5 class="avatar-name"><?php echo get_comment_author_link(); ?></h5>
					<small><?php printf(__('%1$s at %2$s' , 'layerswp' ), get_comment_date(),  get_comment_time()) ?></small>
				</div>
			</div>

			<div class="copy small">
				<?php if ($comment->comment_approved == '0') : ?>
					<em><?php _e('Your comment is awaiting moderation.' , 'layerswp' ) ?></em>
					<br />
				<?php endif; ?>
				<?php comment_text() ?>
				<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>
		<?php if( 2 < $depth && isset( $GLOBALS['lastdepth'] ) && $depth == $GLOBALS['lastdepth'] ) { ?>
			</div>
		<?php } ?>

		<?php $GLOBALS['lastdepth'] = $depth; ?>
	<?php }
} // layers_comment

/**
*  Adjust the site title for static front pages
*/
if( !function_exists( 'layers_post_class' ) ) {
	function layers_post_class( $classes ) {

		$classes[] = 'container';

		if( is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) {
			$classes[] = 'column';
            $classes[] = 'span-4';
		}

		return $classes;
	}
}
add_filter( 'post_class' , 'layers_post_class' );

/**
* Post Featured Media
*
* @param int $attachmentid ID for attachment
* @param int $size Media size to use
* @param int $video oEmbed code
*
* @return   string     $media_output Feature Image or Video
*/

if( !function_exists( 'layers_post_featured_media' ) ) {
	function layers_post_featured_media( $args = array() ){
		global $post;
		$defaults = array (
			'postid' => $post->ID,
			'wrap' => 'div',
			'wrap_class' => 'thumbnail',
			'size' => 'medium'
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$post_meta = get_post_meta( $postid, 'layers', true );

		$featured_media = layers_get_feature_media( get_post_thumbnail_id( $postid ), $size, ( isset( $post_meta[ 'video-url' ] ) ? $post_meta[ 'video-url' ] : NULL ), $postid );

		if( NULL == $featured_media ) return;

		$output = '';

		if( NULL != $featured_media ){
			$output .= $featured_media;
		}

		if( !isset( $hide_href ) && !isset( $post_meta[ 'video-url' ] ) && ( !is_single() && !is_page_template( 'template-blog.php' ) ) ){
			$output = '<a href="' .get_permalink( $postid ) . '">' . $output . '</a>';
		}

		if( '' != $wrap ) {
			$output = '<'.$wrap. ( '' != $wrap_class ? ' class="' . $wrap_class . '"' : '' ) . '>' . $output . '</' . $wrap . '>';
		}

		return $output;
	}
} // layers_post_featured_media