<?php
/*
Plugin Name: Lenix Post External Link
Plugin URI: https://lenix.co.il
Author:  Lenix
Version: 1.0.0
Description: Take any post to external link
Text Domain: lenix-post-external-link
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

function lenix_pel_add_external_link_meta_box(){
    add_meta_box("external-link-meta-box", "External link Meta Box", "lenix_pel_external_link_meta_box_markup", "post", "side", "high", null);
}

add_action("add_meta_boxes", "lenix_pel_add_external_link_meta_box");
////

function lenix_pel_external_link_meta_box_markup($object){
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    ?>
        <div>
            <label for="link_out">External link</label>
            <input name="link_out" type="text" value="<?php echo get_post_meta($object->ID, "link_out", true); ?>">
        </div>
    <?php  
}
///
function lenix_pel_save_external_link_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "post";
    if($slug != $post->post_type)
        return $post_id;

    $meta_box_text_value = "";

    if(isset($_POST["link_out"]))
    {
        $meta_box_text_value = $_POST["link_out"];
    }   
	
	$meta_box_text_value = sanitize_text_field( $_POST['link_out'] );
	
    update_post_meta($post_id, "link_out", $meta_box_text_value);

}

add_action("save_post", "lenix_pel_save_external_link_meta_box", 10, 3);



/// External permalink

if(!is_admin()){
	add_filter( 'post_link', 'lenix_pel_external_permalink', 10, 2 );

	//  Redirect to custom Url
	add_action("template_redirect", "lenix_pel_lenix_redirect");
	
	function lenix_pel_lenix_redirect( ){
		if(is_single()){
			global $post;
			$external_link = get_post_meta( $post->ID, 'link_out', true );
			if( !empty( $external_link ) ) {
				wp_redirect($external_link);
				exit;
			}
		}
	}
	
}
function lenix_pel_external_permalink( $permalink, $post ){
	
    $external_link = get_post_meta( $post->ID, 'link_out', true );
    if( !empty( $external_link ) ) {
        $permalink = $external_link.'"'.'data-external="1';
    }
    return $permalink;
}


/// Js -  adding _blank to a

add_action('wp_footer',function(){
	?>
	<script>
	jQuery(document).on('click','a[data-external="1"]',function(){
		jQuery(this).attr('target','_blank');
		/*var site_url = '<?php echo site_url(); ?>';
		a = jQuery(this);
		link = jQuery(a).attr('href');
		e.preventDefault();*/
	});
	</script>
	<?php
});




