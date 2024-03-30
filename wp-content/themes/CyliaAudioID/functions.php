<?php
/*This file is part of CyliaAudioID, astra child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
	function CyliaAudioID_enqueue_child_styles() {
	    // loading parent style
	    wp_register_style(
	      'parente2-style',
	      get_template_directory_uri() . '/style.css'
	    );

	    wp_enqueue_style( 'parente2-style' );
	    // loading child style
	    wp_register_style(
	      'childe2-style',
	      get_stylesheet_directory_uri() . '/style.css'
	    );
	    wp_enqueue_style( 'childe2-style');
	 }
}
add_action( 'wp_enqueue_scripts', 'CyliaAudioID_enqueue_child_styles' );

//Write here your own functions //
// Published & Modified Date
//function be_published_modified_date() {
//        $date = get_the_date( 'U' );
//        $updated = get_the_modified_date( 'U' );
//        $output = '<span class="entry-date">';
//        //$output .= '<span class="label" style="color:white">Published :  ' . get_the_date( 'F j, Y' ) . '</span>';
//        $output .= '<h2 style="color:white">Published :  ' . get_the_date( 'F j, Y' ) . '</h2>';
//        if( $updated > ( $date + 0*DAY_IN_SECONDS ) ){
//                $output .= '<span class="label">Last updated :  ' . get_the_modified_date( 'F j, Y' ) . '</span>';
//        }
//	$output .= '</span>';
//        return $output;
//}
//add_shortcode( 'be_published_modified_date', 'be_published_modified_date' );

add_filter( 'wpseo_breadcrumb_single_link' ,'wpseo_remove_breadcrumb_link', 10 ,2);

function wpseo_remove_breadcrumb_link( $link_output , $link ){
    global $post;
    $text_to_remove = 'Home';
    $me = home_url();
    if(!strpos($me, 'archives')){
      return $link_output;  // untouched elements
    }

    if( $link['text'] == $text_to_remove ) {
      $countryString = '';  // for upgrade if structure adds a profiles parent for archive

      if(strlen(rtrim(get_page_link(), '/') ) == strlen($me)){
        $parentId = $post->post_parent;
        $linkToParent = get_permalink($parentId);
        $titleToParent = get_the_title($parentId);

        //$countryString = ' » <a href="'.$linkToParent.'">'.$titleToParent.'</a>';
        $countryString = ' » '. $titleToParent;
      }
      // append Home as root directory
      $link_output = '<a href="https://cyliaaudioid.com">Home</a> » <a href="https://cyliaaudioid.com/caribbean-music">Caribbean Music</a>'. $countryString;

    }
 
    return $link_output;
}

function riga_hide_admin_bar(){
    $post = get_post();
    if( $post ){
         if ($post->ID == 166 ){     // backoffice post ID
         return false;
         }
    }
}
add_filter( 'show_admin_bar' , 'riga_hide_admin_bar' );
