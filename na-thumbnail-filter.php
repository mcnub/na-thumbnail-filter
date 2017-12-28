<?php
/**
 * Plugin Name: Native Articles Thumbnail Filter
 * Plugin URI: https://github.com/mcnub/na-thumbnail-filter
 * Description: 
 * Version: 1.0.0
 * Author: Mcnub
 * Author URI: http://github.com/mcnub
 * License: GPL2v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'na_thumbnail_filter_menu');

function na_thumbnail_filter_menu() {
    add_management_page(
        'Native Articles Thumbnail Filter',
        'Native Articles Thumbnail Filter',
        'edit_posts',
        'na_thumbnail_filter_menu',
        'na_thumbnail_filter_callback'
    );
    
    add_action(	'admin_init',
        'register_na_thumbnail_filter_settings'
    );
}

function register_na_thumbnail_filter_settings() {
    register_setting	(	'na-thumbnail-filter',
        'na_thumbnail_filter'
    );
}

function na_thumbnail_filter_callback() {
    global $_wp_additional_image_sizes;
    
    if ( !current_user_can( 'edit_posts' ) ) {
        wp_die( __("You're not cool enough to access this page."));
    }

    ?>
        
        <div class="wrap">
            <h2><?php _e('Set Image Size for Native Articles', 'na-thumbnail-filter'); ?></h2>
            
            <p><?php _e('This will change the URL for native articles.'); ?></p>
            
             <form method="post" action="options.php">
                <?php
                settings_fields( 'na-thumbnail-filter' );
                do_settings_sections( 'na-thumbnail-filter' );
                
                $na_thumbnail_filter = esc_attr( get_option( 'na_thumbnail_filter' ) );
                ?>
             
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Image size name', 'na-thumbnail-filter'); ?></th>
                        <td>
                            <select name="na_thumbnail_filter">
                                <option disabled></option>
                                <?php foreach ( $_wp_additional_image_sizes as $key => $value ): ?>
                                    <option <?php echo ( $key == $na_thumbnail_filter ) ? 'selected="true"' : ''; ?>><?php echo $key; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button() ;?>
             </form>
        </div>
        
    <?php
}

function na_get_attachment_url( $image_url = '' ) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
    return $attachment[0]; 
}

add_filter( 'wpna_facebook_post_get_the_featured_image', 'na_thumbnail_filter_featured_image' );

function na_thumbnail_filter_featured_image( $image_filter ){
    $image = null;
    
    $attachment_id = ( na_get_attachment_url( $image_filter['url'] ) );
    
    $img_props = wp_get_attachment_image_src( $attachment_id, esc_attr( get_option( 'na_thumbnail_filter' ) ) );
    
    if ( is_array( $img_props ) ) {

        // Create a handy array of info.
        $image = array(
            'url'             => $img_props[0],
            'width'           => $img_props[1],
            'height'          => $img_props[2],
            'is_intermediate' => $img_props[3],
            'caption'         => null,
        );

        // Add the caption in if there is one.
        if ( $attachment = get_post( $attachment_id ) && ! empty( $attachment->excerpt ) ) {
            $image['caption'] = $attachment->excerpt;
        }
    }
    
    return $image;
}
