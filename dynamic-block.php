<?php
/**
 * Plugin Name:       Example Dynamic Block
 * Description:       Example dynamic block written with ESNext standard and JSX support – build step required.
 * Requires at least: 5.7
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Abu
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dynamic-block
 *
 * @package           gutenberg-examples
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Register Dynamic Block function
 *
 * @return void
 */
function register_dynamic_block_action() {

	// register main js file from build
	wp_register_script(
		'my-first-dynamic-gutenberg-block-script',
		plugins_url( '/build/index.js', __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-plugins',
			'wp-edit-post',
		),
		true
	);

	// register stylesheet from build folder
	wp_register_style(
		'my-first-dynamic-gutenberg-block-script-css',
		plugins_url( '/build/style-index.css', __FILE__ ),
	);

	// register block on backend
	register_block_type(
		'my-first-dynamic-gutenberg-block/latest-post',
		// define attributes and attributes should be same name from index.js file
		array(
			'attributes'      => array(
				'numberPosts'       => array(
					'type' => 'string',
				),
				'numberColumns'     => array(
					'type'    => 'number',
					'default' => 3,
				),
				'selectedCategory'  => array(
					'type' => 'string',
				),
				'showPostThumbnail' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showExcerpt'       => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showFeaturedPosts' => array(
					'type' => 'boolean',
				),
				'categories'        => array(
					'type' => 'object',
				),
			),
			'render_callback' => 'my_plugin_render_block_latest_post',
			'editor_script'   => 'my-first-dynamic-gutenberg-block-script',
			'style'           => 'my-first-dynamic-gutenberg-block-script-css',
		)
	);
}
add_action( 'init', 'register_dynamic_block_action' );

/**
 * Renders the block content
 */
function my_plugin_render_block_latest_post( $attributes ) {
	// assign variables
	$cat_name              = isset( $attributes['selectedCategory'] ) ? $attributes['selectedCategory'] : '';
	$number_posts_per_page = isset( $attributes['numberPosts'] ) ? $attributes['numberPosts'] : '3';
	if ( $attributes['showFeaturedPosts'] ) {
		$meta_key   = '_checkbox_check';
		$meta_value = 'yes';
	}

	$terms_year = array(
		'post_type' => 'post',
	);

	$years      = array();
	$query_year = new WP_Query( $terms_year );

	// loop start to get categories
	if ( $query_year->have_posts() ) :
		while ( $query_year->have_posts() ) :
			$query_year->the_post();
			$year = get_the_date( 'Y' );
			if ( ! in_array( $year, $years, true ) ) {
				$years[] = $year;
			}
		endwhile;
	endif;
	wp_reset_postdata();
	// loop ends here

	// get transient
	$posts = get_transient( 'my_queries' );
	$args  = array(
		'cat'            => $cat_name,
		'posts_per_page' => $number_posts_per_page,
		'meta_query'     => array(
			array(
				'key'     => $meta_key,
				'value'   => $meta_value,
				'compare' => '=',
			),
		),
	);

	// check if tramsient exists ??
	if ( false === $posts || is_user_logged_in() ) {
		$posts = new WP_Query( $args );
		set_transient( 'my_queries', $posts, HOUR_IN_SECONDS );
	}

	ob_start();
	if ( $posts->have_posts() ) {
		?>
		<div class="search-sort-filters">
			<form id="search-form-filter" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
			<?php wp_nonce_field( 'submit_form', 'my_nonce' ); ?>
			<select name="post_year">
				<?php
				foreach ( $years as $key => $value ) {
					?>
					<option key="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php
				}
				?>
			</select>
			<input type="search" name="post_name" class="post-name" placeholder="Search posts here..." />
			<input type="hidden" name="action" value="post_filter" >
			</form>
		</div>
		<div class="latest-posts-block has-<?php echo isset( $attributes['numberColumns'] ) ? esc_attr( $attributes['numberColumns'] ) : '3'; ?>-columns">
			<?php
			while ( $posts->have_posts() ) {
				$posts->the_post();
				frontend_html_output( $attributes );
			}
			?>
		</div>
		<?php
	} else {
		echo 'No Posts Found';
	}
	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * Output Frontend
 */
function frontend_html_output( $attributes ) {
	?>
		
	<div class="latest-post">
		
		<a href='<?php echo esc_url( get_the_permalink() ); ?>' target='_blank'><h4><?php echo esc_html( get_the_title() ); ?></h4></a>
		<?php
		if ( $attributes['showPostThumbnail'] ) {
			echo get_the_post_thumbnail( '', 'medium' );
		}
		if ( $attributes['showExcerpt'] ) {
			echo esc_html( the_excerpt() );
		}
		?>
	</div>
	<?php
}

/**
 * create meta box render callback output function
 */
function meta_box_markup( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'checkbox_nonce' );
	$checkbox_stored_meta = get_post_meta( $post->ID );
	?>
	
	<label for="_checkbox_check">
		<input type="checkbox" name="_checkbox_check" id="_checkbox_check" value="yes" 
		<?php
		if ( isset( $checkbox_stored_meta ['_checkbox_check'] ) ) {
			checked( $checkbox_stored_meta['_checkbox_check'][0], 'yes' );}
		?>
		/>
		<?php esc_html_e( 'Feature This Post ?' ); ?>
	</label>
	<?php
}

/**
 *  Save metabox markup per post/page
 */
function save_custom_meta_box( $post_id ) {
	$is_autosave             = wp_is_post_autosave( $post_id );
	$is_revision             = wp_is_post_revision( $post_id );
	$is_valid_nonce_checkbox = filter_input( INPUT_POST, 'checkbox_nonce', FILTER_SANITIZE_STRING );
	$is_valid_nonce          = ( isset( $is_valid_nonce_checkbox ) && wp_verify_nonce( $is_valid_nonce_checkbox, basename( __FILE__ ) ) ) ? 'true' : 'false';

	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		 return;
	}

	if ( isset( $_POST['_checkbox_check'] ) ) {
		update_post_meta( $post_id, '_checkbox_check', 'yes' );
	} else {
		update_post_meta( $post_id, '_checkbox_check', '' );
	}
}
add_action( 'save_post', 'save_custom_meta_box', 10, 1 );

/**
 *  Add Metabox per post/page and any registered custom post type
 */
function add_custom_meta_box() {
	$post_types = 'post';
	add_meta_box( 'checkbox-meta-box', __( 'Featured Post' ), 'meta_box_markup', $post_types, 'advanced', 'default', null );
}
add_action( 'add_meta_boxes', 'add_custom_meta_box' );

/**
 * Enqueing Ajax JS File
 */
function frontend_scripts() {

	wp_register_script( 'ajaxfilehandle', MY_PLUGIN_URL . 'assets/js/ajax.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'ajaxfilehandle' );
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'frontend_scripts' );

/**
 * including ajax call file
 */
require_once MY_PLUGIN_PATH . 'inc/ajax-call.php';

/**
 * Delete transient
 */
function delete_my_recipes_transient() {
	delete_transient( 'my_queries' );
}
add_action( 'save_post', 'delete_my_recipes_transient' );
add_action( 'delete_post', 'delete_my_recipes_transient' );
