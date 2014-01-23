<?php

/**
 * Load plugin css
 */
add_action( 'wp_enqueue_scripts', 'yt_load_plugin_css' );

function yt_load_plugin_css(){
       
        wp_enqueue_style( 'plugin-css', plugins_url( '/_inc/plugin.css', __FILE__), false, '1.0.0', 'all' );
    }
    
/**
 * Load a template
 * @param type $template
 */
function bp_yt_load_template( $template ) {

    if ( is_readable( STYLESHEETPATH . '/' . $template ) )
        $load = STYLESHEETPATH . '/' . $template;
    elseif ( is_readable(TEMPLATEPATH . '/' . $template ) )
        $load = TEMPLATEPATH . '/' . $template;
    elseif ( is_readable( STYLESHEETPATH . '/yt-theme-compat/' . $template ) )
        $load = STYLESHEETPATH . '/yt-theme-compat/' . $template;
    elseif ( is_readable( TEMPLATEPATH . '/yt-theme-compat/' . $template ) )
        $load = TEMPLATEPATH . '/yt-theme-compat/' . $template;
    else //if not found, always load form 
        $load = BP_YT_PLUGIN_DIR . 'yt-theme-compat/' . $template;

    include $load;
}

//check if main file should be loaded from theme or plugin,. only affects loading of yt/home.php
function bp_yt_is_using_theme_compat(){
    static $using_compat;
    
    if( isset ( $using_compat ) )
        return $using_compat;
    
    if( file_exists( TEMPLATEPATH . '/yt' ) || file_exists( STYLESHEETPATH . '/yt' ) )
            $using_compat = false;
   
    else
        $using_compat = true;
  
    return $using_compat;
    
}

//this function returns the generated content for Youtube feed plugin
function bp_yt_get_page_content(){
    
    bp_yt_load_template( 'yt/index.php' );
    
}

function bp_yt_get_group_page_content(){
    
    bp_yt_load_template( 'yt/groups/index.php' );
    
}