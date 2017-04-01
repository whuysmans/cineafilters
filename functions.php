<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

require_once('helpers.php');
/** Various clean up functions */
require_once( 'fp-library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'fp-library/foundation.php' );

/** Register all navigation menus */
require_once( 'fp-library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'fp-library/menu-walkers.php' );

/** Create widget areas in sidebar and footer */
// require_once( 'fp-library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'fp-library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'fp-library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'fp-library/theme-support.php' );

/** Add Nav Options to Customer */
// require_once( 'fp-library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'fp-library/sticky-posts.php' );

/** Configure responsive image sizes */
require_once( 'fp-library/responsive-images.php' );

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/protocol-relative-theme-assets.php' );




// CON IMPETO BASE FUNCTIONS
require_once(get_template_directory().'/assets/functions/little-helpers.php');
// require_once(get_template_directory().'/assets/functions/extras/protected-roles.php');

require_once(get_template_directory().'/assets/functions/architecture.php');
// require_once(get_template_directory().'/assets/functions/extras/widgets.php');
require_once(get_template_directory().'/assets/functions/extras/custom-shortcodes.php');

require_once(get_template_directory().'/assets/functions/admin-cleanup.php'); 
require_once(get_template_directory().'/assets/functions/output-cleanup.php');
require_once(get_template_directory().'/assets/functions/template-tags.php');





// PROJECT SPECIFICS
require_once(get_template_directory().'/assets/functions/project.php');

function write_log ( $log ) {
    if ( true === WP_DEBUG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}

function ci_custom_scripts () {

  if ( is_home() ) {
    wp_register_script( 'ci-awesomplete-js', get_template_directory_uri() . '/assets/components/awesomplete/awesomplete.js', array(), '1.1', true );
    wp_register_style( 'ci-awesomplete-css', get_template_directory_uri() . '/assets/components/awesomplete/awesomplete.css', array( 'main-stylesheet' ), '1.2', 'all' );
    wp_register_script( 'ci-custom-filter', get_template_directory_uri() . '/assets/javascript/custom/ci-custom-filter.js', array( 'jquery', 'ci-awesomplete-js' ), '2', true );
    wp_enqueue_script( 'ci-awesomplete-js' );
    wp_enqueue_style( 'ci-awesomplete-css' );
    wp_enqueue_script( 'ci-custom-filter' );
    wp_localize_script( 'ci-custom-filter', 'custom_filter', array (
      'ajax_url' => admin_url( 'admin-ajax.php' )
    ) );
  }
}
add_action( 'wp_enqueue_scripts', 'ci_custom_scripts', 9999 );

//retrieve the defined filters (custom taxonomies en custom relationships posts-events)
function get_filters () {

  $allowed = \Util\Params::getAllowedFilters();
  echo json_encode( $allowed );
  wp_die();
}

add_action( 'wp_ajax_nopriv_get_filters', 'get_filters' );
add_action( 'wp_ajax_get_filters', 'get_filters' );

//retrieve the filtered posts
function get_results() {
  $params =  isset( $_POST[ 'data' ] ) ? $_POST[ 'data' ] : null;
  $result = \Util\Queries::getAllowedPosts( $params );
  echo $result ;
  wp_die();
}

add_action( 'wp_ajax_nopriv_get_results', 'get_results' );
add_action( 'wp_ajax_get_results', 'get_results' );




