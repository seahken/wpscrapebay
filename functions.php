<?php 

wpscrapeaby_deactivate();

add_action('daily_event','wpscrapebay_schedule_tasks');

function wpscrapebay_schedule_tasks(){

	global $database_added;

	for ($i=0; $i<count($database_added); $i++){ 

			$post_content = wpscrapebay_getProductInfo($database_added[$i]['link']);

			$database_added[$i]['post_id'] = wpscrapebay_programmatically_create_post( $database_added[$i]['name'],
																$database_added[$i]['post_id'],
																$post_content['body'],
																$post_content['condition'],													
																$post_content['price'],																
																$post_content['image_url'],
																$post_content['availability'],
																$post_content['category'],
																'pending'
																);

			$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
			update_option('wpscrapebay',$wpscrapebay_options);

			
		}

		
}

function wpscrapeaby_activate() {
	wp_schedule_event( time(), 'wpscrapebay_daily', 'daily_event');
}

function wpscrapeaby_deactivate() {
	wp_clear_scheduled_hook('daily_event');
}

function wpscrapebay_get_html($url){

	$feed_url = $url;
	$args = array('timeout'=>120);

	$html_feed = wp_remote_get($feed_url,$args);

	return $html_feed;
}

function wpscrapebay_get_html_body($url){

	$feed_url = $url;
	$args = array('timeout'=>120);

	$html_feed = wp_remote_get($feed_url,$args);

	return $html_feed['body'];
}

function wpscrapebay_scrape($url){
    $output = file_get_contents($url); 
    return $output;
}

function wpscrapebay_scrapeHTMLDocument ($url) {
    $page = new DOMDocument();
    $html = wpscrapebay_scrape($url);
    $page->loadHTML($html);
    return $page;
}

function wpscrapebay_scrapeHTMLDocumentFeed ($url) {
    $page = new DOMDocument();
    $html = wpscrapebay_get_html($url);
    $page->loadHTML($html['body']);
    return $page;
}

function wpscrapebay_getAttributeValueAndLink($url, $tagName, $attribute, $className){
	$page = wpscrapebay_scrapeHTMLDocumentFeed($url);
    $properties = $page->getElementsByTagName($tagName);
    $k = 0;
    foreach ($properties as $property){          

        if ($property->getAttribute($attribute) == $className){ 
            $linkElements = $property->getElementsByTagName('a');
            foreach ($linkElements as $linkElement){
            $link = $linkElement->getAttribute('href');
            $name = $linkElement->getAttribute('title'); 
            }  

            $database[$k]['name'] = $name;
            $database[$k]['link'] = $link;
            $database[$k]['id'] = wpscrapebay_after_last('/', $link);
            
            $k++;
        }

    }
    return $database;
}

function wpscrapebay_getHTMLContent($url, $tagName, $attribute, $className){
	$page = wpscrapebay_scrapeHTMLDocument($url);
    
    return wpscrapebay_getNodeHTMLFromElementClass($page, $tagName, $attribute, $className);
}

function wpscrapebay_getProductInfo($url){
	$page = wpscrapebay_scrapeHTMLDocumentFeed($url);	
    $productInfo['condition'] = wpscrapebay_getNodeValueFromElementClass($page, 'div', 'id', 'vi-itm-cond');    
    $productInfo['body'] = wpscrapebay_getIframeURL_content($page);
    $productInfo['postage'] = trim(wpscrapebay_getNodeValueFromElementClass($page, 'span', 'id', 'fshippingCost'), " \t\n\r\0\x0B");
    $productInfo['postage'] = floatval(number_format( substr($productInfo['postage'],4), 2, '.', '' ));

    $productInfo['price'] = wpscrapebay_getNodeValueFromElementClass($page, 'span', 'id', 'prcIsum');

    if($productInfo['price'] == NULL){
	    $productInfo['price'] = wpscrapebay_getNodeValueFromElementClass($page, 'span', 'id', 'mm-saleOrgPrc');
	    $productInfo['sale_price'] = wpscrapebay_getNodeValueFromElementClass($page, 'span', 'id', 'mm-saleDscPrc');
	    $productInfo['sale_price'] = floatval(number_format( substr($productInfo['sale_price'],4), 2, '.', '' ));
	    $productInfo['sale_price'] += $productInfo['postage'];
	}

    $productInfo['price'] = floatval(number_format( substr($productInfo['price'],4), 2, '.', '' ));
    $productInfo['price'] += $productInfo['postage'];

    $productInfo['availability'] = trim(wpscrapebay_getNodeValueFromElementClass($page, 'span', 'id', 'qtySubTxt'), " \t\n\r\0\x0B");    
    $productInfo['image_url'] = wpscrapebay_getImageURL($page);  
    
    $productInfo['category'] = wpscrapebay_getEbayCategories($page);

    
    return $productInfo;
}

function wpscrapebay_getNodeHTMLFromElementClass ($page, $tagName, $attribute ,$className){
	$properties = $page->getElementsByTagName($tagName);
    foreach ($properties as $property){ 
    	if ($property->getAttribute($attribute) == $className){
    		$htmlContent = wpscrapebay_get_inner_html($property->parentNode); // In order to get the entire div 
    	}
    }

    return $htmlContent;

}

function wpscrapebay_getNodeValueFromElementClass($page, $tagName,$attribute,$className){
	$properties = $page->getElementsByTagName($tagName);
    foreach ($properties as $property){ 
    	if ($property->getAttribute($attribute) == $className){
    		return $property->nodeValue; // In order to get the entire div 
    		die();
    	}
    }
}

function wpscrapebay_getNodeValuesFromElementClass($page, $tagName,$attribute,$className){
	$properties = $page->getElementsByTagName($tagName);
	$k = 0;
    foreach ($properties as $property){ 
    	if ($property->getAttribute($attribute) == $className){
    		$nodeContent[$k] =  $property->nodeValue;  
    		$k++;
    	}
    }
    return $nodeContent;
}

function wpscrapebay_getEbayCategories($page){
	$properties = $page->getElementsByTagName('ul');
	$k = 0;

    foreach ($properties as $property){ 
    	if ($property->getAttribute('itemtype') == 'http://schema.org/Breadcrumblist'){
    		$links = $property->getElementsByTagName('span');
    		$j = 0;
    		foreach($links as $link){
    			if ($link->getAttribute('itemprop') == 'name'){
    				$nodeContent[$k][$j] =  $link->nodeValue; 
    				$j++;
    			}
    		}
    		$k++;
    		
    	}
    }
    return $nodeContent;
}

function wpscrapebay_getNodeValuesFromTag($page,$tagName){
	$properties = $page->getElementsByTagName($tagName);
	$i = 0;
		foreach ($properties as $property){
			$property_numbers[$i] = $property->nodeValue;
			$i ++;
		}

	return $property_numbers; 
}

function wpscrapebay_getIframeURL_content($page){
	$properties = $page->getElementsByTagName('iframe');
	$i = 0;
	foreach($properties as $property){
		if ($property->getAttribute('id') == 'desc_ifr'){
			$iFrameURL = $property->getAttribute('src');
		}
	}

	$iFrameContent = wpscrapebay_scrapeHTMLDocument($iFrameURL);
	return wpscrapebay_getNodeHTMLFromElementClass($iFrameContent, 'div', 'id', 'ds_div');
	

}

function wpscrapebay_getImageURL($page){
	$properties = $page->getElementsByTagName('img');
	$low_resolution = array('_14','_35');
	$k = 0;
		foreach ($properties as $property){

			$contains_thumbnails = strpos($property->getAttribute('src'), '$_14.JPG');
			$contains_fullsize = strpos($property->getAttribute('src'), '$_12.JPG');

			if ($property->getAttribute('id') == 'icImg'){
				$image_url_number[$k] = $property->getAttribute('src');
				$image_url_number[$k] = str_replace($low_resolution,'_12',$image_url_number[$k]);
				
			} 

			if ($contains_thumbnails != false){
				$image_url[$k] = $property->getAttribute('src');
				$image_url[$k] = str_replace($low_resolution,'_12',$image_url[$k]);
				$k ++;
			} 
		}


		if ($image_url != null){
			for ($i=0; $i<(count($image_url))/3; $i++ ){
				$image_url_number[$i] = $image_url[$i];
			}
		}

	return $image_url_number; 
}

function wpscrapebay_getNodeValuesFromTagAndClass($page,$tagName,$attribute,$className){
	$properties = $page->getElementsByTagName($tagName);
	$i = 0;
		foreach ($properties as $property){
			if ($property->getAttribute($attribute) == $className){
				$property_numbers[$i] = wpscrapebay_removeTextFromBehind($property->nodeValue, 'Save to Calendar');
				$i ++;
			}
		}

	return $property_numbers; 
}

function wpscrapebay_removeTextFromBehind($string, $removeString){
	return rtrim($string,$removeString);
}

function wpscrapebay_get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; // Get all childnodes of target node
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML;  
}

function wpscrapebay_programmatically_create_post($post_title, $post_uid, $post_content, $product_condition, $product_price, $sale_price, $image_url, $availability, $category, $post_status) {

	// Initialize the page ID to . This indicates no action has been taken.
	$post_id = $post_uid;
	$excerpt = 'Price inclusive delivery Australia-wide';
	
	global $wpdb;

	// Setup the author, slug, and title for the post
	$author_id = 1;
	$title = $post_title;
	$post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $post_uid . "'", 'ARRAY_A');
	//$post_exists = get_post_meta($post_id, 'property_unique_id') == $property_id; 

	// If the post doesn't already exist, then create it
	if( /*null == get_post( $post_uid*/ $post_exists == false)  {

		// Set the post ID so that we know the post was created successfully
		$post_id = wp_insert_post(
			array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	$author_id,
				'post_title'		=>	$title,
				'post_status'		=>	$post_status,
				'post_type'			=>	'product',
				'post_content'  	=>  $post_content,
				'post_category'		=>  array($category),
				'post_excerpt'		=>	$excerpt
			)
		);

		
		add_post_meta($post_id, '_regular_price' , $product_price);
		add_post_meta($post_id, 'product_type' , 'simple');
		
		add_post_meta($post_id, '_price' , $product_price);
		add_post_meta($post_id, '_total_sales' ,'0');
		add_post_meta($post_id, '_stock_status' , 'instock');
		add_post_meta($post_id, '_visibility' , 'visible');

		if ($sale_price != NULL){
			add_post_meta($post_id, '_sale_price' , $sale_price);
			update_post_meta($post_id, '_price' , $sale_price);
		}


		$k=0;
		for ($j=0;$j<count($category);$j++){
		
			for ($i=0;$i<count($category[$j]);$i++){

				if(term_exists($category[$j][$i],'product_cat') != NULL){
					$term = term_exists($category[$j][$i],'product_cat');
					$category_id[$k] = intval($term['term_id']);
					$k++;
					
				} else {

					if ($i == 0){
						$term = wp_insert_term($category[$j][$i], 'product_cat');
						$category_id[$k] = intval($term['term_id']);
						$k++;
											
						
					} else {
						$term_args = ['parent' => $category_id[$k-1]];
						$term = wp_insert_term($category[$j][$i], 'product_cat', $term_args);
						$category_id[$k] = intval($term['term_id']);
						$k++;
					}
				}
			}
		}
		
		
		wp_set_object_terms($post_id, $category_id, 'product_cat');
		
		wpscrapebay_fetch_media($image_url[0], $post_id, true);
		
	

		
	// Otherwise, we'll update post
	} else { 

		$post_id = wp_insert_post(
			array(
				'ID'				=> $post_uid,
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	$author_id,
				'post_title'		=>	$title,
				'post_status'		=>	$post_status,
				'post_type'			=>	'product',
				'post_content'  	=>  $post_content,
				'post_excerpt'		=>	$excerpt
				)
			);

			
			update_post_meta($post_uid, '_regular_price' , $product_price);
			update_post_meta($post_id, 'product_type' , 'simple');
			update_post_meta($post_uid, '_price', $product_price);
			update_post_meta($post_uid, '_total_sales' , '0');
			update_post_meta($post_id, '_stock_status' , 'instock');
			update_post_meta($post_id, '_visibility' , 'visible');


			if ($sale_price != NULL){
				update_post_meta($post_id, '_sale_price' , $sale_price);
				update_post_meta($post_uid, '_price', $sale_price);
			}

			$k=0;
			for ($j=0;$j<count($category);$j++){
			
				for ($i=0;$i<count($category[$j]);$i++){

					if(term_exists($category[$j][$i],'product_cat') != NULL){
						$term = term_exists($category[$j][$i],'product_cat');
						$category_id[$k] = intval($term['term_id']);
						$k++;
						
					} else {

						if ($i == 0){
							$term = wp_insert_term($category[$j][$i], 'product_cat');
							$category_id[$k] = intval($term['term_id']);
							$k++;
												
							
						} else {
							$term_args = ['parent' => $category_id[$k-1]];
							$term = wp_insert_term($category[$j][$i], 'product_cat', $term_args);
							$category_id[$k] = intval($term['term_id']);
							$k++;
						}
					}
				}
			}
			
			wp_set_object_terms($post_uid, $category_id, 'product_cat');
			
			$post_id = $post_uid;

				

	} // end if
	return $post_id;
} // end wpscrapebay_programmatically_create_post

function wpscrapebay_fetch_media($file_url, $post_id, $feature_image) {
	require_once(ABSPATH . 'wp-load.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	global $wpdb;

	if(!$post_id) {
		return false;
	}

	//directory to import to	
	$artDir = 'wp-content/uploads/importedmedia/';

	//if the directory doesn't exist, create it	
	if(!file_exists(ABSPATH.$artDir)) {
		mkdir(ABSPATH.$artDir);
	}

	//rename the file... alternatively, you could explode on "/" and keep the original file name
	$ext = array_pop(explode(".", $file_url));
	$new_filename = 'blogmedia-'.$post_id.".".$ext; //if your post has multiple files, you may need to add a random number to the file name to prevent overwrites

	if (@fclose(@fopen($file_url, "r"))) { //make sure the file actually exists
		copy($file_url, ABSPATH.$artDir.$new_filename);

		$siteurl = get_option('siteurl');
		$file_info = getimagesize(ABSPATH.$artDir.$new_filename);

		//create an array of attachment data to insert into wp_posts table
		$artdata = array();
		$artdata = array(
			'post_author' => 1, 
			'post_date' => current_time('mysql'),
			'post_date_gmt' => current_time('mysql'),
			'post_title' => $new_filename, 
			'post_status' => 'inherit',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $new_filename)),											
			'post_modified' => current_time('mysql'),
			'post_modified_gmt' => current_time('mysql'),
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'guid' => $siteurl.'/'.$artDir.$new_filename,
			'post_mime_type' => $file_info['mime'],
			'post_excerpt' => '',
			'post_content' => ''
		);

		$uploads = wp_upload_dir();
		$save_path = $uploads['basedir'].'/importedmedia/'.$new_filename;

		//insert the database record
		$attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );

		//generate metadata and thumbnails
		if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
			wp_update_attachment_metadata($attach_id, $attach_data);
		}

		if ($feature_image == true){//optional make it the featured image of the post it's attached to
			$rows_affected = $wpdb->insert($wpdb->prefix.'postmeta', array('post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id));
		}
	}
	else {
		return false;
	}

	return true;
}

function wpscrapebay_fetchdata($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to wpscrapebay_scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to wpscrapebay_scrape
        return $data;   // Returning the scraped data from the function
}

function wpscrapebay_after_last ($this, $inthat)
{
    if (!is_bool(wpscrpebay_strrevpos($inthat, $this))){
    return substr($inthat, wpscrpebay_strrevpos($inthat, $this)+strlen($this));
	}
}

function wpscrpebay_strrevpos($instr, $needle)
{
    $rev_pos = strpos (strrev($instr), strrev($needle));
    if ($rev_pos===false) return false;
    else return strlen($instr) - $rev_pos - strlen($needle);
}

?>