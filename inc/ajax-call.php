<?php
/**
 * Ajax Call for search filter
 *
 * @return void
 */
function filter_post_asb() {

	if ( isset( $_POST['my_nonce'] ) &&
	wp_verify_nonce( sanitize_text_field( $_POST['my_nonce'] ), 'submit_form' ) ) {

		$post_year = isset( $_POST['post_year'] ) ? sanitize_text_field( $_POST['post_year'] ) : '';
		$post_name = isset( $_POST['post_name'] ) ? sanitize_text_field( $_POST['post_name'] ) : '';

		$args    = array(
			'post_type' => 'post',
			'year'      => $post_year,
			's'         => $post_name,
		);
		$queries = new WP_Query( $args );

		if ( ! empty( $queries ) ) {
					$html = array_map(
						function ( $query ) {
							return array(
								'thumbnail' => esc_url( get_the_post_thumbnail_url( $query->ID, 'medium' ) ),
								'title'     => esc_html( get_the_title( $query->ID ) ),
								'permalink' => esc_url( get_permalink( $query->ID ) ),
								'excerpt'   => esc_html( get_the_excerpt( $query->ID ) ),
							);
						},
						$queries->get_posts()
					);
		}
		echo wp_json_encode( $html );

		wp_die();
	}
}

add_action( 'wp_ajax_post_filter', 'filter_post_asb' );
add_action( 'wp_ajax_nopriv_post_filter', 'filter_post_asb' );
