<?php
/**
 * Copy all existing images to S3
 */
function ms_copy_post_images_to_s3($post_id) {    
  $output = '';
  $output .= "Updating Post: ".$post_id.'<br/>';
  
  // update featured image
  $featured_image_id = get_post_meta($post_id, '_thumbnail_id', true);
  if ($featured_image_id) {
   $attach_src = wp_get_attachment_image_src($featured_image_id, 'full');
    
    // if featured image not on S3
    if (stripos($attach_src[0], 's3.amazonaws.com') === false) {
      $attach_data = wp_get_attachment_metadata($featured_image_id);
      $output .= 'Featured Image: '.$attach_src[0].'<br/>';
      wp_update_attachment_metadata( $featured_image_id, $attach_data );
    }
  }
  
  // add imageHero, imageMedium, imageSmall
  $imageHero = get_post_meta($post_id, 'imageHero', true);
  if ($imageHero && $imageHero !== 'MG') {
    $output .= "Hero: ".$imageHero.'<br/>';
    if (stripos($imageHero, 's3.amazonaws.com') === false) {
      $attach_id = ms_save_resized_image_to_uploads($imageHero);
      
      update_post_meta($post_id, 'imageHero', ms_get_image_url($attach_id, MS_IMAGE_SIZE_HERO));
    }
  }
  $imageMedium = get_post_meta($post_id, 'imageMedium', true);
  if ($imageMedium) {
    $output .= "Medium: ".$imageMedium.'<br/>';
    if (stripos($imageMedium, 's3.amazonaws.com') === false) {
      $attach_id = ms_save_resized_image_to_uploads($imageMedium);
      
      update_post_meta($post_id, 'imageMedium', ms_get_image_url($attach_id, MS_IMAGE_SIZE_MEDIUM));
    }
  }
  $imageSmall = get_post_meta($post_id, 'imageSmall', true);
  if ($imageSmall) {
    $output .= "Small: ".$imageSmall.'<br/>';
    if (stripos($imageSmall, 's3.amazonaws.com') === false) {
      $attach_id = ms_save_resized_image_to_uploads($imageSmall);
      
      update_post_meta($post_id, 'imageSmall', ms_get_image_url($attach_id, MS_IMAGE_SIZE_SMALL));
    }
  }  
  
  $imageThumbnail = get_post_meta($post_id, 'imageThumbnail', true);
  if ($imageSmall) {
    $output .= "Thumbnail: ".$imageThumbnail.'<br/>';
    if (stripos($imageThumbnail, 's3.amazonaws.com') === false) {
      $attach_id = ms_save_resized_image_to_uploads($imageThumbnail);
      
      update_post_meta($post_id, 'imageThumbnail', ms_get_image_url($attach_id, MS_IMAGE_SIZE_SMALL));
    }
  }         
  
  // if has gallery meta, upload those media
  $gallery_items_number = get_post_meta($post_id, 'galleryItemsNumber', true);
  if ($gallery_items_number !== false) {
    for ($gallery_item_id = 0; $gallery_item_id < $gallery_items_number; $gallery_item_id++) {
      $gallery_img_src = get_post_meta($post_id, 'gallery_img_'.$gallery_item_id, true);
      $output .= 'Gallery img: '.$gallery_img_src.'<br/>';
      if (stripos($$gallery_img_src, 's3.amazonaws.com') === false) {
        update_post_meta($post_id, 'gallery_img_'.$gallery_item_id, ms_save_image_to_uploads($gallery_img_src));
      }
    }
  }
  
  return $output;
}


/**
 * ajax callback to update next post
 * 
 * @access public
 * @return void
 */
function ms_images_update_next_ajax_cb() {
  $result = array('success'=>false, 'next_id'=>-1);
  $posts = get_posts(array('posts_per_page'=>-1, 'post_status'=>array('publish', 'draft'), 'post_type'=>'articles'));
  
  $next_id = isset($_POST['next_id']) ? $_POST['next_id'] : 0;
  
  if (!empty($posts)) {
    $result['debug_output'] = '';
    
    // if next_id is 0, then we set it to the first post
    if (intval($next_id) === 0) {
      $post = reset($posts);
      $next_id = $post->ID;
    }
    else {
      // get the post
      $post = reset($posts);
      while ($post && intval($next_id) !== $post->ID) {
        $post = next($posts);
      }
    }
    
    // copy to s3
    $result['debug_output'] .= ms_copy_post_images_to_s3($post->ID);
    
    // update next id
    $next_id = null;
    $next_post = reset($posts);
    do {
      $current_post = $next_post;
      
      $next_post = next($posts);
      
      //$result['debug_output'] .= '<br/> Comparing :'.$current_post->ID.' vs '.$post->ID;
      
      if ($current_post->ID === $post->ID) {
        $next_id = $next_post->ID;
      }
    } while ($next_post && !$next_id);

    $result['next_id'] = $next_id === null ? -1 : $next_id;
  }
  
  echo json_encode($result);
  wp_die();
}

add_action( 'wp_ajax_ms_images_update_next', 'ms_images_update_next_ajax_cb' );


/**
 * add admin menu.
 * 
 * @access public
 * @return void
 */
function ms_images_add_admin_menu() {
  add_submenu_page( 'edit.php?post_type=articles', 'Update images to S3', 'Update images to S3', 'manage_options', 'update-images-to-s3', 'ms_images_display_submenu_cb' );
}

add_action('admin_menu', 'ms_images_add_admin_menu');


/**
 * display the submenu page
 * 
 * @access public
 * @return void
 */
function ms_images_display_submenu_cb() {
  if (isset($_POST['image-test'])) {
    ms_run_images_test();
  }
  
  ?>
  <div class="wrap">
    <div id="icon-tools" class="icon32"></div>
	  <h2>Update images on articles to S3</h2>
	  <p>Updates all articles' images to be on S3</p>
	  <p id="update-status"></p>
	  <p id="update-action"></p>
	  <button type="button" id="update_images">Update images</button>
	  
	  <form action="" method="post">
	  <button type="submit" name="image-test" value="test">Test</button>
	  </form>
	</div>
	<?php
}


/**
 * testing image upload.
 * 
 * @access public
 * @return void
 */
function ms_run_images_test() {
  $image_url = 'http://editorial.mansionglobal.com/wp-content/uploads/2015/05/BN-HZ507_0424FO_GR_20150420184130.jpg';
  
  echo $image_url;
  
  $attach_id = ms_save_resized_image_to_uploads($image_url);
  
  // get the URL - this should have been moved to S3 if using the Amazon S3 plugin
  $attach_src = ms_get_image_url($attach_id, MS_IMAGE_SIZE_HERO);
  
  echo "<pre>";                  
  print_r($attach_src);
  echo "</pre>";
  
  $attach_src = ms_get_image_url($attach_id, MS_IMAGE_SIZE_MEDIUM);
  
  echo "<pre>";                  
  print_r($attach_src);
  echo "</pre>";
  
  $attach_src = ms_get_image_url($attach_id, MS_IMAGE_SIZE_SMALL);
  
  echo "<pre>";                  
  print_r($attach_src);
  echo "</pre>";
  
  die();

}


/**
 * add the javascript for the admin menu
 * 
 * @access public
 * @return void
 */
function ms_images_submenu_javascript() {
  ?>
  <script type="text/javascript">
    
  (function($) {
  	  	
  	$(document).ready(function() {
    	
    	$('#update_images').click(function(evt) {
      	var next_id = 0;
      	
      	// update the status text
      	function update_status(json) {
        	var message = json.next_id === -1 ? 'Finished' : 'Updated post '+json.next_id;
        	$('#update-action').html(message);
        	
        	if (typeof json.debug_output !== 'undefined') {
          	message = '<br/>Output: '+json.debug_output;
          }
        	
        	$('#update-status').html(message);
      	}
      	
      	// run the json post
      	function run_next_update() {
        	var data = {
          	action: 'ms_images_update_next',
          	next_id: next_id
        	};
        	
        	console.log('posting to: '+next_id);
      	
          $('#update-action').html('Updating: post '+next_id);
        	$.post(ajaxurl, data, function(response) {
          	var json = JSON.parse(response);
          	
          	console.log('Next ID: '+json.next_id);
          	
          	if (json) {	
            	update_status(json);
            	
            	next_id = json.next_id;
            	
            	// if we've not finished
          	  if (next_id !== -1) {
            	  // loop
            	  run_next_update();
              }
            }
        	});
        }
        
        // initiate
        run_next_update();
      	
    	});
  	});
  	
  })(jQuery);
  </script>
<?php
}

add_action( 'admin_footer', 'ms_images_submenu_javascript' );