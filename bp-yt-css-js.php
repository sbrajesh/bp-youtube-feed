<?php

//include css
add_action( 'wp_print_styles', 'bp_yt_enqueue_css' );

//load css
function bp_yt_enqueue_css() {
   // wp_enqueue_style("pgcss",BP_YT_PLUGIN_URL."css/prettyPhoto.css");
     if ( file_exists( STYLESHEETPATH . '/yt/style.css' ) )
            $theme_uri = get_stylesheet_directory_uri();//child theme
    else if ( file_exists( TEMPLATEPATH . '/yt/style.css' ) )
	    $theme_uri = get_template_directory_uri();//parent theme

    if( ! empty( $theme_uri ) ) {
        $stylesheet_uri = $theme_uri . '/yt/style.css';
        wp_register_style( 'ytcss', $stylesheet_uri );
        wp_enqueue_style( 'ytcss' );
    }
}
