<?php
/**
 *	Kolor
 *
 *	Theme by: Art Ramadani
 *	Developed by: Arlind Nushi
 *
 *	www.laborator.co
 */

# Get Template File being in use
function template_file()
{
	global $template;
	
	return basename($template);
}

# Excerpt Length
function silicon_excerpt_length()
{
	return 80;
}

# Excerpt Length (for 2 columns blog posts)
function silicon_excerpt_length_2()
{
	return 40;
}

# Excerpt more string (appended in the end)
function silicon_excerpt_more()
{
	return '...';
}


# Comment Form Default Fields
function silicon_comment_fields($fields)
{	
	foreach($fields as $field_type => $field_html)
	{
		preg_match("/<label(.*?)>(.*?)<\/label>/", $field_html, $html_label);
		preg_match("/<input(.*?)\/>/", $field_html, $html_input);
		
		$html_label = $html_label[0];
		$html_input = $html_input[0];
		
		$fields[$field_type] = "<div class=\"placeholder comment_field\">
	{$html_label}
	{$html_input}
</div>";
	}
	
	
	return $fields;
}

function silicon_comment_before_fields()
{
	echo '<div class="author_details">';
}

function silicon_comment_after_fields()
{
	echo '</div>';
}


# List Comments Loop
function silicon_list_comments($comment, $args, $depth)
{
	global $post, $wpdb;
		
	$comment_ID 			= $comment->comment_ID;
	$comment_author 		= $comment->comment_author;
	$comment_author_url		= $comment->comment_author_url;
	$comment_author_email	= $comment->comment_author_email;
	$comment_date 			= $comment->comment_date;
	$comment_content 		= $comment->comment_content;
	
	$avatar					= preg_replace("/\s?(height='[0-9]+'|width='[0-9]+')/", "", get_avatar($comment));
	
	$comment_timespan 		= human_time_diff(strtotime($comment_date), time());
	
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php echo $comment_ID; ?>">
		
		<?php 
		if($depth > 1):
			$comment_parent_id = $wpdb->get_col("SELECT comment_parent FROM {$wpdb->comments} WHERE comment_ID = '{$comment_ID}'");
			$comment_parent_id = reset($comment_parent_id);
			
			if($comment_parent_id)
			{
				$parent_comment = get_comment($comment_parent_id);
		
				?>
				<div class="in_reply_to">
					<?php echo sprintf(__('in reply to %s', TD), '<a href="#">' . $parent_comment->comment_author . '</a> <span class="comment_id">#' . $depth . '</a>'); ?>
				</div>
				<?php 
			}
				
		endif; 
		?>
		
		<div id="comment-<?php echo $comment_ID; ?>" class="comment">
			
			<div class="image">
				<?php echo $avatar; ?>
			</div>
			
			<div class="comment_text">
				<i class="arrow"></i>
				
				<div class="comment_header">
					<a href="<?php echo $comment_author_url ? $comment_author_url : ($comment_author_url . '#comment-' . $comment_ID); ?>" class="author"><?php echo $comment_author; ?></a>
					<span class="timespan">(<?php echo $comment_timespan . ' ' . __('ago', TD); ?>)</span>
					
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', TD), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ), $comment, $post ); ?>
					<?php edit_comment_link('<i class="icon-edit"></i> ' . __('Edit'), '', ''); ?>
				</div>
				
				<div class="comment_content">
					<?php echo wpautop($comment_content); ?>
				</div>
				
			</div>
			
		</div>
		
	<?php
}


/* Filter: Post Password Form */
function silicon_post_password_form($output)
{
	global $post;
	
	$warn_text = __("This post is password protected. To view it please enter your password below:", TD);
	
	$label = 'pwbox-' . ( empty($post->ID) ? rand() : $post->ID );
	
	$_output = '';
	
	if(get_option('silicon_pass_protected_show_excerpt') == 'true'):
	
		$words_num = get_option('silicon_pass_protected_stories_words_num');
		$words_num = $words_num ? $words_num : 55;
		$post_content = wp_trim_words($post->post_content, $words_num);
		
		$_output = '<div class="excerpt_pre_pass">' . $post_content . '</div>';
		
	endif;
	
	$_output .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post" class="password_protected_post">
	<p>' . get_option('silicon_pass_protected_stories_text_hint') . '</p>
	<div class="placeholder"><label for="' . $label . '">' . __("Password:") . '</label> <input name="post_password" id="' . $label . '" type="password" size="20" class="input" /> <button type="submit" class="button">' . esc_attr(get_option('silicon_pass_protected_stories_button_text')) . '</button></div>
</form>
	';
	
	return $_output;
}



/* Function to generate Pagination From-To Numbers */
function pagination_generate_from_to($current_page, $max_num_pages, $numbers_to_show = 5)
{
	$add_sub_1 = round($numbers_to_show/2);
	$add_sub_2 = round($numbers_to_show - $add_sub_1);
	
	$from = $current_page - $add_sub_1;
	$to = $current_page + $add_sub_2;
	
	$limits_exceeded_l = FALSE;
	$limits_exceeded_r = FALSE;
	
	if($from < 1)
	{
		$from = 1;
		$limits_exceeded_l = TRUE;
	}
	
	if($to > $max_num_pages)
	{
		$to = $max_num_pages;
		$limits_exceeded_r = TRUE;
	}
	
	
	if($limits_exceeded_l)
	{
		$from = 1;
		$to = $numbers_to_show;
	}
	else
	if($limits_exceeded_r)
	{
		$from = $max_num_pages - $numbers_to_show + 1;
		$to = $max_num_pages;
	}
	else
	{
		$from += 1;
	}
	
	if($from < 1)
		$from = 1;
	
	if($to > $max_num_pages)
	{
		$to = $max_num_pages;
	}
	
	
	return array($from, $to);
}



/* Get Portfolio Options function (for page) */
function get_portfolio_options($get_post_id = 0)
{
	global $post;
	
	$post_id = $get_post_id ? $get_post_id : get_the_ID();
	
	$items_per_page 			= get_post_meta($post_id, 'items_per_page', TRUE);
	$pagination_position		= get_post_meta($post_id, 'pagination_position', TRUE);
	$pagination_type			= get_post_meta($post_id, 'pagination_type', TRUE);
	
	$disable_isotope 			= get_post_meta($post_id, 'disable_isotope', TRUE);
	
	
	$portfolio_columns 			= get_post_meta($post_id, 'portfolio_columns', TRUE);
	$portfolio_bottom_titles 	= get_post_meta($post_id, 'portfolio_bottom_titles', TRUE);
	
	$browse_from_category	 	= get_post_meta($post_id, 'browse_from_category', TRUE);
	
	# Process Vars
	if( ! is_numeric($items_per_page) || $items_per_page < 2)
		$items_per_page = 12;
	
	# Build Options Array
	$portfolio_options = array(
		'disable_isotope' 			=> $disable_isotope,
		
		#'allow_likes' 				=> $allow_likes,
		#'like_logging_method'		=> $like_logging_method,
		#'portfolio_unlike_support'	=> $portfolio_unlike_support,
		
		#'show_views' 				=> $show_views,
		
		# Pagination
		'items_per_page' 			=> $items_per_page,
		'pagination_position'		=> $pagination_position,
		'numbers_to_show'			=> 5,
		'pagination_type'			=> $pagination_type,
		
		# Portfolio Template
		'columns'					=> $portfolio_columns,
		'portfolio_bottom_titles'	=> FALSE,
		
		# Categories
		'browse_from_category'		=> $browse_from_category
	);
	

	# Set Columns Number
	if($portfolio_columns)
	{
		#$portfolio_options['columns'] = $portfolio_columns;
	}
	
	return $portfolio_options;
}


/* Get Gallery Options function (for page) */
function get_gallery_options($get_post_id = 0)
{
	global $post;
	
	$post_id = $get_post_id ? $get_post_id : get_the_ID();
	
	$items_per_page 			= get_post_meta($post_id, 'items_per_page', TRUE);
	$pagination_position		= get_post_meta($post_id, 'pagination_position', TRUE);
	$pagination_type			= get_post_meta($post_id, 'pagination_type', TRUE);
	
	$gallery_view				= get_post_meta($post_id, 'gallery_view', TRUE);
	$hide_view_options			= get_post_meta($post_id, 'hide_view_options', TRUE);
	
	
	
	# Process Vars
	if( ! is_numeric($items_per_page) || $items_per_page < 2)
		$items_per_page = 12;
	
	# Build Options Array
	$portfolio_options = array(
	
		# Pagination
		'items_per_page' 			=> $items_per_page,
		'pagination_position'		=> $pagination_position,
		'numbers_to_show'			=> 5,
		'pagination_type'			=> $pagination_type,
		
		# View Options
		'gallery_view'				=> $gallery_view,
		'hide_view_options'			=> $hide_view_options,
	);
	
	return $portfolio_options;
}


/* Get Gallery Options function (for page) */
function get_blog_options($get_post_id = 0)
{
	global $post;
	
	$post_id = $get_post_id ? $get_post_id : get_the_ID();
	
	$blog_columns	 			= get_post_meta($post_id, 'blog_columns', TRUE);
	$items_per_page 			= get_post_meta($post_id, 'items_per_page', TRUE);
	$pagination_position		= get_post_meta($post_id, 'pagination_position', TRUE);
	
	$browse_from_category		= get_post_meta($post_id, 'browse_from_category', TRUE);
	
	
	
	# Process Vars
	if( ! is_numeric($items_per_page) || $items_per_page < 2)
		$items_per_page = 12;
	
	# Build Options Array
	$portfolio_options = array(
	
		# Pagination
		'items_per_page' 			=> $items_per_page,
		'pagination_position'		=> $pagination_position,
		'numbers_to_show'			=> 5,
		#'pagination_type'			=> $pagination_type,
		
		'blog_columns'				=> $blog_columns,
		'browse_from_category'		=> $browse_from_category,
		
	);
	
	return $portfolio_options;
}



# Detect Youtube and Vimeo Links
function get_video_frames_links($text)
{
	if(strstr($text, "youtube.com"))
	{
		preg_match("#v=([\w\-]+)#", $text, $youtube_video_id);
		$youtube_video_id = end($youtube_video_id);
		
		$href = "http://www.youtube.com/v/{$youtube_video_id}";
		
		return $href;
	}
	else
	# Detect Vimeo Link
	if(strstr($text, "vimeo.com"))
	{
		preg_match("#com/(\d+)#", $text, $vimeo_video_id);
		$vimeo_video_id = end($vimeo_video_id);
		
		$href = "http://vimeo.com/moogaloop.swf?clip_id={$vimeo_video_id}";
		
		return $href;
	}
	
	return null;
}


# Check if wpml is installed and running
function is_wpml()
{
	return function_exists('icl_get_home_url') && defined('ICL_LANGUAGE_CODE') ? TRUE : FALSE;
}


# Custom CSS Styling
function show_custom_css()
{
	$custom_css = get_option('silicon_custom_css');
	
	$break_point_1 = get_option('silicon_custom_css_bp_1');
	$break_point_2 = get_option('silicon_custom_css_bp_2');
	$break_point_3 = get_option('silicon_custom_css_bp_3');
	$break_point_4 = get_option('silicon_custom_css_bp_4');
	
	if(strlen($custom_css . $break_point_1 . $break_point_2 . $break_point_3 . $break_point_4))
	{
		# Get URLS
		preg_match_all("/(http(.*?)\.css)/", $custom_css, $urls);
		
		$urls = $urls[1];
		
		$custom_css = str_replace($urls, '', $custom_css);
		
		$custom_css = preg_replace('/\s{3,}/', ' ', $custom_css);
		
		foreach($urls as $css_url)
		{
			echo '<link rel="stylesheet" type="text/css" href="' . $css_url . '">' . PHP_EOL;
		}
		
		?>
<style type="text/css">
<?php echo $custom_css; ?>

/* Tablet Portrait size to standard 960 (devices and browsers) */
@media only screen and (min-width: 768px) and (max-width: 959px) {
	<?php echo $break_point_1; ?>

}

/* All Mobile Sizes (devices and browser) */
@media only screen and (max-width: 767px) {
	<?php echo $break_point_2; ?>

}

/* Mobile Landscape Size to Tablet Portrait (devices and browsers) */
@media only screen and (min-width: 480px) and (max-width: 767px) {
	<?php echo $break_point_3; ?>

}

/* Mobile Portrait Size to Mobile Landscape Size (devices and browsers) */
@media only screen and (max-width: 479px) {
	<?php echo $break_point_4; ?>

}

</style>
<?php
	}
}



# WPB Composer Translate Column Width
function laborator_wpb_translate_column_spans($width)
{	
	switch ( $width ) 
	{
		case "1/6" :
			$w = 'three columns'; # Skeleton
			break;
		
		case "4":
		case "1/4" :
			$w = 'four columns'; # Skeleton
			break;
		
		case "3":
		case "1/3" :
			$w = 'one-third column'; # Skeleton
			break;
		
		case "2":
		case "1/2":
			$w = 'eight columns'; # Skeleton
			break;
		
		case "2/3" :
			$w = 'two-thirds column'; # Skeleton
			break;
		
		case "3/4" :
			$w = 'twelve columns'; # Skeleton
			break;
		
		case "5/6" :
			$w = 'thirteen columns'; # Skeleton
			break;
		
		case "1":
		case "1/1" :
			$w = 'sixteen columns'; # Skeleton
			break;
			
		
		default:
			$w = $width;
	}
	
	return $w;
}


# WPB Composer Translate Row Class
function laborator_wpb_translate_row_class()
{
	return 'row';
}


# Header Widget [Hardcoded]
function silicon_get_header_widget($type)
{
	switch($type)
	{
		case 'search':
			?>
			<!-- search -->	
			<!--
<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ) ; ?>">
				<input type="text" id="s" name="s" placeholder="<?php _e('Search site...', TD); ?>" value="<?php echo get_search_query(); ?>" />
				<input type="submit" id="searchsubmit" value="<?php echo esc_attr__('Search', TD); ?>" />
			</form>
-->
			<p class='address'><span><h2>Call Us Today ! <font size="5", face="times, serif">(305) 763-8114</font></h2></span> 960 Arthur Godfrey Road #322  
Miami Beach, FL 33140</p>
			<!-- end: search -->
			<?php
			break;
		
		case 'social_networks':
			
			?>
			<div class="social_networks_header">
				<?php DISPLAY_ACURAX_ICONS(); ?>
				<!-- <?php echo LABSC_SocialNetworks::getSocialIcons(13, 'header'); ?> -->
				
			</div>
			<?php
			
			break;
			
		default:
			?>
			&nbsp;
			<?php
	}
}


function icon_collection_list()
{
	#$icon_list = array('icon-glass', 'icon-music', 'icon-search', 'icon-envelope', 'icon-heart', 'icon-star', 'icon-star-empty', 'icon-user', 'icon-film', 'icon-th-large', 'icon-th', 'icon-th-list', 'icon-ok', 'icon-remove', 'icon-zoom-in', 'icon-zoom-out', 'icon-off', 'icon-signal', 'icon-cog', 'icon-trash', 'icon-home', 'icon-file', 'icon-time', 'icon-road', 'icon-download-alt', 'icon-download', 'icon-upload', 'icon-inbox', 'icon-play-circle', 'icon-repeat', 'icon-refresh', 'icon-list-alt', 'icon-lock', 'icon-flag', 'icon-headphones', 'icon-volume-off', 'icon-volume-down', 'icon-volume-up', 'icon-qrcode', 'icon-barcode', 'icon-tag', 'icon-tags', 'icon-book', 'icon-bookmark', 'icon-print', 'icon-camera', 'icon-font', 'icon-bold', 'icon-italic', 'icon-text-height', 'icon-text-width', 'icon-align-left', 'icon-align-center', 'icon-align-right', 'icon-align-justify', 'icon-list', 'icon-indent-left', 'icon-indent-right', 'icon-facetime-video', 'icon-picture', 'icon-pencil', 'icon-map-marker', 'icon-adjust', 'icon-tint', 'icon-edit', 'icon-share', 'icon-check', 'icon-move', 'icon-step-backward', 'icon-fast-backward', 'icon-backward', 'icon-play', 'icon-pause', 'icon-stop', 'icon-forward', 'icon-fast-forward', 'icon-step-forward', 'icon-eject', 'icon-chevron-left', 'icon-chevron-right', 'icon-plus-sign', 'icon-minus-sign', 'icon-remove-sign', 'icon-ok-sign', 'icon-question-sign', 'icon-info-sign', 'icon-screenshot', 'icon-remove-circle', 'icon-ok-circle', 'icon-ban-circle', 'icon-arrow-left', 'icon-arrow-right', 'icon-arrow-up', 'icon-arrow-down', 'icon-share-alt', 'icon-resize-full', 'icon-resize-small', 'icon-plus', 'icon-minus', 'icon-asterisk', 'icon-exclamation-sign', 'icon-gift', 'icon-leaf', 'icon-fire', 'icon-eye-open', 'icon-eye-close', 'icon-warning-sign', 'icon-plane', 'icon-calendar', 'icon-random', 'icon-comment', 'icon-magnet', 'icon-chevron-up', 'icon-chevron-down', 'icon-retweet', 'icon-shopping-cart', 'icon-folder-close', 'icon-folder-open', 'icon-resize-vertical', 'icon-resize-horizontal', 'icon-bar-chart', 'icon-twitter-sign', 'icon-facebook-sign', 'icon-camera-retro', 'icon-key', 'icon-cogs', 'icon-comments', 'icon-thumbs-up', 'icon-thumbs-down', 'icon-star-half', 'icon-heart-empty', 'icon-signout', 'icon-linkedin-sign', 'icon-pushpin', 'icon-external-link', 'icon-signin', 'icon-trophy', 'icon-github-sign', 'icon-upload-alt', 'icon-lemon', 'icon-phone', 'icon-check-empty', 'icon-bookmark-empty', 'icon-phone-sign', 'icon-twitter', 'icon-facebook', 'icon-github', 'icon-unlock', 'icon-credit-card', 'icon-rss', 'icon-hdd', 'icon-bullhorn', 'icon-bell', 'icon-certificate', 'icon-hand-right', 'icon-hand-left', 'icon-hand-up', 'icon-hand-down', 'icon-circle-arrow-left', 'icon-circle-arrow-right', 'icon-circle-arrow-up', 'icon-circle-arrow-down', 'icon-globe', 'icon-wrench', 'icon-tasks', 'icon-filter', 'icon-briefcase', 'icon-fullscreen', 'icon-group', 'icon-link', 'icon-cloud', 'icon-beaker', 'icon-cut', 'icon-copy', 'icon-paper-clip', 'icon-save', 'icon-sign-blank', 'icon-reorder', 'icon-list-ul', 'icon-list-ol', 'icon-strikethrough', 'icon-underline', 'icon-table', 'icon-magic', 'icon-truck', 'icon-pinterest', 'icon-pinterest-sign', 'icon-google-plus-sign', 'icon-google-plus', 'icon-money', 'icon-caret-down', 'icon-caret-up', 'icon-caret-left', 'icon-caret-right', 'icon-columns', 'icon-sort', 'icon-sort-down', 'icon-sort-up', 'icon-envelope-alt', 'icon-linkedin', 'icon-undo', 'icon-legal', 'icon-dashboard', 'icon-comment-alt', 'icon-comments-alt', 'icon-bolt', 'icon-sitemap', 'icon-umbrella', 'icon-paste', 'icon-lightbulb', 'icon-exchange', 'icon-cloud-download', 'icon-cloud-upload', 'icon-user-md', 'icon-stethoscope', 'icon-suitcase', 'icon-bell-alt', 'icon-coffee', 'icon-food', 'icon-file-alt', 'icon-building', 'icon-hospital', 'icon-ambulance', 'icon-medkit', 'icon-fighter-jet', 'icon-beer', 'icon-h-sign', 'icon-plus-sign-alt', 'icon-double-angle-left', 'icon-double-angle-right', 'icon-double-angle-up', 'icon-double-angle-down', 'icon-angle-left', 'icon-angle-right', 'icon-angle-up', 'icon-angle-down', 'icon-desktop', 'icon-laptop', 'icon-tablet', 'icon-mobile-phone', 'icon-circle-blank', 'icon-quote-left', 'icon-quote-right', 'icon-spinner', 'icon-circle', 'icon-reply', 'icon-github-alt', 'icon-folder-close-alt', 'icon-folder-open-alt');
	$icon_list = array('icon-music', 'icon-music-alt', 'icon-search', 'icon-search-alt', 'icon-mail', 'icon-heart', 'icon-heart-empty', 'icon-star', 'icon-star-empty', 'icon-user', 'icon-users', 'icon-user-add', 'icon-video', 'icon-picture', 'icon-camera', 'icon-th', 'icon-th-list', 'icon-ok', 'icon-cancel', 'icon-cancel-circle', 'icon-plus', 'icon-plus-sign', 'icon-minus', 'icon-minus-circle', 'icon-help', 'icon-help-circle', 'icon-info', 'icon-info-circle', 'icon-back', 'icon-back-alt', 'icon-home', 'icon-link', 'icon-attach', 'icon-lock', 'icon-lock-open', 'icon-eye', 'icon-tag', 'icon-bookmark', 'icon-flag', 'icon-thumbs-up', 'icon-download', 'icon-upload', 'icon-upload-cloud', 'icon-reply', 'icon-reply-all', 'icon-forward', 'icon-quote-right', 'icon-code', 'icon-export', 'icon-pencil', 'icon-feather', 'icon-print', 'icon-retweet', 'icon-keyboard', 'icon-comment', 'icon-chat', 'icon-bell', 'icon-attention', 'icon-vcard', 'icon-address', 'icon-map-marker', 'icon-map', 'icon-direction', 'icon-compass', 'icon-trash', 'icon-doc', 'icon-docs', 'icon-docs-landscape', 'icon-doc-text', 'icon-book-open', 'icon-folder', 'icon-archive', 'icon-rss', 'icon-phone', 'icon-cog', 'icon-share', 'icon-basket', 'icon-calendar', 'icon-mic', 'icon-volume-off', 'icon-volume-up', 'icon-volume', 'icon-clock', 'icon-hourglass', 'icon-lamp', 'icon-light-down', 'icon-light-up', 'icon-block', 'icon-resize-full', 'icon-resize-small', 'icon-popup', 'icon-publish', 'icon-window', 'icon-arrow-combo', 'icon-down-circle2', 'icon-left-circle2', 'icon-right-circle2', 'icon-up-circle2', 'icon-down-open', 'icon-chevron-left', 'icon-chevron-right', 'icon-up-open', 'icon-down-thin', 'icon-left-thin', 'icon-right-thin', 'icon-up-thin', 'icon-down-dir', 'icon-left-dir', 'icon-right-dir', 'icon-up-dir', 'icon-down-bold', 'icon-left-bold', 'icon-right-bold', 'icon-up-bold', 'icon-down', 'icon-left', 'icon-right', 'icon-up', 'icon-ccw', 'icon-cw', 'icon-level-down', 'icon-shuffle', 'icon-play', 'icon-stop', 'icon-pause', 'icon-record', 'icon-to-end', 'icon-to-start', 'icon-fast-fw', 'icon-fast-bw', 'icon-progress-0', 'icon-progress-1', 'icon-progress-2', 'icon-progress-3', 'icon-target', 'icon-palette', 'icon-list-add', 'icon-signal', 'icon-top-list', 'icon-battery', 'icon-back-in-time', 'icon-monitor', 'icon-mobile', 'icon-net', 'icon-cd', 'icon-inbox', 'icon-install', 'icon-globe', 'icon-cloud', 'icon-flash', 'icon-moon', 'icon-flight', 'icon-leaf', 'icon-lifebuoy', 'icon-mouse', 'icon-bag', 'icon-dot', 'icon-dot-2', 'icon-dot-3', 'icon-google-circles', 'icon-cc', 'icon-logo-entypo', 'icon-flag-sw', 'icon-logo-db');
	
	#$icon_list = array('icon phone', ' icon mobile', ' icon mouse', ' icon address', ' icon mail', ' icon paper-plane', ' icon pencil', ' icon feather', ' icon attach', ' icon inbox', ' icon reply', ' icon reply-all', ' icon forward', ' icon user', ' icon users', ' icon add-user', ' icon vcard', ' icon export', ' icon location', ' icon map', ' icon compass', ' icon direction', ' icon hair-cross', ' icon share', ' icon shareable', ' icon heart', ' icon heart-empty', ' icon star', ' icon star-empty', ' icon thumbs-up', ' icon thumbs-down', ' icon chat', ' icon comment', ' icon quote', ' icon home', ' icon popup', ' icon search', ' icon flashlight', ' icon print', ' icon bell', ' icon link', ' icon flag', ' icon cog', ' icon tools', ' icon trophy', ' icon tag', ' icon camera', ' icon megaphone', ' icon moon', ' icon palette', ' icon leaf', ' icon note', ' icon beamed-note', ' icon new', ' icon graduation-cap', ' icon book', ' icon newspaper', ' icon bag', ' icon airplane', ' icon lifebuoy', ' icon eye', ' icon clock', ' icon mic', ' icon calendar', ' icon flash', ' icon thunder-cloud', ' icon droplet', ' icon cd', ' icon briefcase', ' icon air', ' icon hourglass', ' icon gauge', ' icon language', ' icon network', ' icon key', ' icon battery', ' icon bucket', ' icon magnet', ' icon drive', ' icon cup', ' icon rocket', ' icon brush', ' icon suitcase', ' icon traffic-cone', ' icon globe', ' icon keyboard', ' icon browser', ' icon publish', ' icon progress-3', ' icon progress-2', ' icon progress-1', ' icon progress-0', ' icon light-down', ' icon light-up', ' icon adjust', ' icon code', ' icon monitor', ' icon infinity', ' icon light-bulb', ' icon credit-card', ' icon database', ' icon voicemail', ' icon clipboard', ' icon cart', ' icon box', ' icon ticket', ' icon rss', ' icon signal', ' icon thermometer', ' icon water', ' icon sweden', ' icon line-graph', ' icon pie-chart', ' icon bar-graph', ' icon area-graph', ' icon lock', ' icon lock-open', ' icon logout', ' icon login', ' icon check', ' icon cross', ' icon squared-minus', ' icon squared-plus', ' icon squared-cross', ' icon circled-minus', ' icon circled-plus', ' icon circled-cross', ' icon minus', ' icon plus', ' icon erase', ' icon block', ' icon info', ' icon circled-info', ' icon help', ' icon circled-help', ' icon warning', ' icon cycle', ' icon cw', ' icon ccw', ' icon shuffle', ' icon back', ' icon level-down', ' icon retweet', ' icon loop', ' icon back-in-time', ' icon level-up', ' icon switch', ' icon numbered-list', ' icon add-to-list', ' icon layout', ' icon list', ' icon text-doc', ' icon text-doc-inverted', ' icon doc', ' icon docs', ' icon landscape-doc', ' icon picture', ' icon video', ' icon music', ' icon folder', ' icon archive', ' icon trash', ' icon upload', ' icon download', ' icon save', ' icon install', ' icon cloud', ' icon upload-cloud', ' icon bookmark', ' icon bookmarks', ' icon open-book', ' icon play', ' icon paus', ' icon record', ' icon stop', ' icon ff', ' icon fb', ' icon to-start', ' icon to-end', ' icon resize-full', ' icon resize-small', ' icon volume', ' icon sound', ' icon mute', ' icon flow-cascade', ' icon flow-branch', ' icon flow-tree', ' icon flow-line', ' icon flow-parallel', ' icon left-bold', ' icon down-bold', ' icon up-bold', ' icon right-bold', ' icon left', ' icon down', ' icon up', ' icon right', ' icon circled-left', ' icon circled-down', ' icon circled-up', ' icon circled-right', ' icon triangle-left', ' icon triangle-down', ' icon triangle-up', ' icon triangle-right', ' icon chevron-left', ' icon chevron-down', ' icon chevron-up', ' icon chevron-right', ' icon chevron-small-left', ' icon chevron-small-down', ' icon chevron-small-up', ' icon chevron-small-right', ' icon chevron-thin-left', ' icon chevron-thin-down', ' icon chevron-thin-up', ' icon chevron-thin-right', ' icon left-thin', ' icon down-thin', ' icon up-thin', ' icon right-thin', ' icon arrow-combo', ' icon three-dots', ' icon two-dots', ' icon dot', ' icon cc', ' icon cc-by', ' icon cc-nc', ' icon cc-nc-eu', ' icon cc-nc-jp', ' icon cc-sa', ' icon cc-nd', ' icon cc-pd', ' icon cc-zero', ' icon cc-share', ' icon cc-remix', ' icon db-logo', ' icon db-shape');
	
	if(get_option('silicon_default_icon_system') == 'fontawesome')
	{
		$icon_list = array('icon-glass', 'icon-music', 'icon-search', 'icon-envelope', 'icon-heart', 'icon-star', 'icon-star-empty', 'icon-user', 'icon-film', 'icon-th-large', 'icon-th', 'icon-th-list', 'icon-ok', 'icon-remove', 'icon-zoom-in', 'icon-zoom-out', 'icon-off', 'icon-signal', 'icon-cog', 'icon-trash', 'icon-home', 'icon-file', 'icon-time', 'icon-road', 'icon-download-alt', 'icon-download', 'icon-upload', 'icon-inbox', 'icon-play-circle', 'icon-repeat', 'icon-refresh', 'icon-list-alt', 'icon-lock', 'icon-flag', 'icon-headphones', 'icon-volume-off', 'icon-volume-down', 'icon-volume-up', 'icon-qrcode', 'icon-barcode', 'icon-tag', 'icon-tags', 'icon-book', 'icon-bookmark', 'icon-print', 'icon-camera', 'icon-font', 'icon-bold', 'icon-italic', 'icon-text-height', 'icon-text-width', 'icon-align-left', 'icon-align-center', 'icon-align-right', 'icon-align-justify', 'icon-list', 'icon-indent-left', 'icon-indent-right', 'icon-facetime-video', 'icon-picture', 'icon-pencil', 'icon-map-marker', 'icon-adjust', 'icon-tint', 'icon-edit', 'icon-share', 'icon-check', 'icon-move', 'icon-step-backward', 'icon-fast-backward', 'icon-backward', 'icon-play', 'icon-pause', 'icon-stop', 'icon-forward', 'icon-fast-forward', 'icon-step-forward', 'icon-eject', 'icon-chevron-left', 'icon-chevron-right', 'icon-plus-sign', 'icon-minus-sign', 'icon-remove-sign', 'icon-ok-sign', 'icon-question-sign', 'icon-info-sign', 'icon-screenshot', 'icon-remove-circle', 'icon-ok-circle', 'icon-ban-circle', 'icon-arrow-left', 'icon-arrow-right', 'icon-arrow-up', 'icon-arrow-down', 'icon-share-alt', 'icon-resize-full', 'icon-resize-small', 'icon-plus', 'icon-minus', 'icon-asterisk', 'icon-exclamation-sign', 'icon-gift', 'icon-leaf', 'icon-fire', 'icon-eye-open', 'icon-eye-close', 'icon-warning-sign', 'icon-plane', 'icon-calendar', 'icon-random', 'icon-comment', 'icon-magnet', 'icon-chevron-up', 'icon-chevron-down', 'icon-retweet', 'icon-shopping-cart', 'icon-folder-close', 'icon-folder-open', 'icon-resize-vertical', 'icon-resize-horizontal', 'icon-bar-chart', 'icon-twitter-sign', 'icon-facebook-sign', 'icon-camera-retro', 'icon-key', 'icon-cogs', 'icon-comments', 'icon-thumbs-up', 'icon-thumbs-down', 'icon-star-half', 'icon-heart-empty', 'icon-signout', 'icon-linkedin-sign', 'icon-pushpin', 'icon-external-link', 'icon-signin', 'icon-trophy', 'icon-github-sign', 'icon-upload-alt', 'icon-lemon', 'icon-phone', 'icon-check-empty', 'icon-bookmark-empty', 'icon-phone-sign', 'icon-twitter', 'icon-facebook', 'icon-github', 'icon-unlock', 'icon-credit-card', 'icon-rss', 'icon-hdd', 'icon-bullhorn', 'icon-bell', 'icon-certificate', 'icon-hand-right', 'icon-hand-left', 'icon-hand-up', 'icon-hand-down', 'icon-circle-arrow-left', 'icon-circle-arrow-right', 'icon-circle-arrow-up', 'icon-circle-arrow-down', 'icon-globe', 'icon-wrench', 'icon-tasks', 'icon-filter', 'icon-briefcase', 'icon-fullscreen', 'icon-group', 'icon-link', 'icon-cloud', 'icon-beaker', 'icon-cut', 'icon-copy', 'icon-paper-clip', 'icon-save', 'icon-sign-blank', 'icon-reorder', 'icon-list-ul', 'icon-list-ol', 'icon-strikethrough', 'icon-underline', 'icon-table', 'icon-magic', 'icon-truck', 'icon-pinterest', 'icon-pinterest-sign', 'icon-google-plus-sign', 'icon-google-plus', 'icon-money', 'icon-caret-down', 'icon-caret-up', 'icon-caret-left', 'icon-caret-right', 'icon-columns', 'icon-sort', 'icon-sort-down', 'icon-sort-up', 'icon-envelope-alt', 'icon-linkedin', 'icon-undo', 'icon-legal', 'icon-dashboard', 'icon-comment-alt', 'icon-comments-alt', 'icon-bolt', 'icon-sitemap', 'icon-umbrella', 'icon-paste', 'icon-lightbulb', 'icon-exchange', 'icon-cloud-download', 'icon-cloud-upload', 'icon-user-md', 'icon-stethoscope', 'icon-suitcase', 'icon-bell-alt', 'icon-coffee', 'icon-food', 'icon-file-alt', 'icon-building', 'icon-hospital', 'icon-ambulance', 'icon-medkit', 'icon-fighter-jet', 'icon-beer', 'icon-h-sign', 'icon-plus-sign-alt', 'icon-double-angle-left', 'icon-double-angle-right', 'icon-double-angle-up', 'icon-double-angle-down', 'icon-angle-left', 'icon-angle-right', 'icon-angle-up', 'icon-angle-down', 'icon-desktop', 'icon-laptop', 'icon-tablet', 'icon-mobile-phone', 'icon-circle-blank', 'icon-quote-left', 'icon-quote-right', 'icon-spinner', 'icon-circle', 'icon-reply');
	}
	
	function cmp_sort($a, $b)
	{
		return $a > $b ? 1 : -1;
	}
	
	uasort($icon_list, 'cmp_sort');
	
	return $icon_list;
}


# GET/POST getter
function get($var)
{
	return isset($_GET[$var]) ? $_GET[$var] : null;
}

function post($var)
{
	return isset($_POST[$var]) ? $_POST[$var] : null;
}

function cookie($var)
{
	return isset($_COOKIE[$var]) ? $_COOKIE[$var] : null;
}


# Reset Query Params function (easier to keep in mind)
function _reset()
{
	wp_reset_query();
	wp_reset_postdata();
}


# Generate From-To numbers borders
function generate_from_to($from, $to, $current_page, $max_num_pages, $numbers_to_show = 5)
{
	if($numbers_to_show > $max_num_pages)
		$numbers_to_show = $max_num_pages;
	
	
	$add_sub_1 = round($numbers_to_show/2);
	$add_sub_2 = round($numbers_to_show - $add_sub_1);
	
	$from = $current_page - $add_sub_1;
	$to = $current_page + $add_sub_2;
	
	$limits_exceeded_l = FALSE;
	$limits_exceeded_r = FALSE;
	
	if($from < 1)
	{
		$from = 1;
		$limits_exceeded_l = TRUE;
	}
	
	if($to > $max_num_pages)
	{
		$to = $max_num_pages;
		$limits_exceeded_r = TRUE;
	}
	
	
	if($limits_exceeded_l)
	{
		$from = 1;
		$to = $numbers_to_show;
	}
	else
	if($limits_exceeded_r)
	{
		$from = $max_num_pages - $numbers_to_show + 1;
		$to = $max_num_pages;
	}
	else
	{
		$from += 1;
	}
	
	if($from < 1)
		$from = 1;
	
	if($to > $max_num_pages)
	{
		$to = $max_num_pages;
	}
	
	return array($from, $to);
}


# Laborator Pagination
function laborator_show_pagination($current_page, $max_num_pages, $from, $to, $pagination_position = 'full', $numbers_to_show = 5)
{
	$current_page = $current_page ? $current_page : 1;
	
	?>
	<div class="clear"></div>
	
	<!-- pagination -->
	<ul class="pagination<?php echo $pagination_position == 'center' ? ' center' : ($pagination_position == 'right' ? ' right' : ($pagination_position == 'full' ? ' full' : '')); ?>"><!-- add class 'center' or 'right' to position the text (default: left) -->		
	
	<?php if($current_page > 1): ?>
		<li class="first_page"><a href="<?php echo get_pagenum_link(1); ?>"><?php _e('First Page', TD); ?></a></li>
	<?php endif; ?>

	<?php
	
	if($from > floor($numbers_to_show / 2))
	{
		?>
		<li><a href="<?php echo get_pagenum_link(1); ?>"><?php echo 1; ?></a></li>
		<li>...</li>
		<?php
	}
	
	for($i=$from; $i<=$to; $i++):
		
		$link_to_page = get_pagenum_link($i);
		$is_active = $current_page == $i;

	?>
		<li<?php echo $is_active ? ' class="active"' : ''; ?>><a href="<?php echo $link_to_page; ?>"><?php echo $i; ?></a></li>
	<?php
	endfor;
		
	
	if($max_num_pages > $to)
	{
		if($max_num_pages != $i):
		?>
			<li>...</li>
		<?php
		endif;
		
		?>
		<li><a href="<?php echo get_pagenum_link($max_num_pages); ?>"><?php echo $max_num_pages; ?></a></li>
		<?php
	}
	?>
	
	<?php if($current_page < $max_num_pages): ?>
		<li class="last_page"><a href="<?php echo get_pagenum_link($max_num_pages); ?>"><?php _e('Last Page', TD); ?></a></li>
	<?php endif; ?>
	</ul>
	<!-- end: pagination -->
	<?php
}


function ord_suffix($a)
{
	$suffix = substr(date('jS', mktime(0,0,0,1,($a%10==0?9:($a%100>20?$a%10:$a%100)),2000)),-2);
	$suffix = str_replace(array('st', 'nd', 'rd', 'th'), array(_x('st', TD, 'Suffix for first'), _x('nd', TD, 'Suffix for second'), _x('rd', TD, 'Suffix for third'), _x('th', TD, 'Suffix for fourth and upper')), $suffix);
	
	return $a.$suffix;
}


function get_google_fonts($alias)
{
	if(get_option('silicon_use_custom_fontface') == 'true')
	{
		?>
		<link href="<?php echo esc_attr(get_option('silicon_custom_fontface_url')); ?>" rel="stylesheet" type="text/css">
		<?php
		return;
	}
	
	switch(strtolower($alias))
	{
		case 'volkorn':
		case 'vollkorn':
			?>
			<link href='http://fonts.googleapis.com/css?family=Vollkorn:400,700' rel="stylesheet" type="text/css">
			<?php
			break;
			
		case 'oswald':
			?>
			<link href='http://fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		case 'nunito':
			?>
			<link href='http://fonts.googleapis.com/css?family=Nunito:400,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		case 'arimo':
			?>
			<link href='http://fonts.googleapis.com/css?family=Arimo:400,700,400italic' rel='stylesheet' type='text/css'>
			<?php
			break;
		
		case 'open-sans':
			?>
			<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
			<?php
			break;
		
		case 'ubuntu':
			?>
			<link href='http://fonts.googleapis.com/css?family=Ubuntu:400,500,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'arvo':
			?>
			<link href='http://fonts.googleapis.com/css?family=Arvo:400,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'exo':
			?>
			<link href='http://fonts.googleapis.com/css?family=Exo:400,500,700,400italic' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'roboto-condensed':
			?>
			<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400italic,400,700,300' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'play':
			?>
			<link href='http://fonts.googleapis.com/css?family=Play:400,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'monsterrat':
			?>
			<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
			<?php
			break;
			
		
		case 'oxygen':
			?>
			<link href='http://fonts.googleapis.com/css?family=Oxygen:400,700,300' rel='stylesheet' type='text/css'>
			<?php
			break;
		
		default:
			?>
			<link href='http://fonts.googleapis.com/css?family=Raleway:400,600,500,700' rel='stylesheet' type='text/css'>
			<?php
	}
}


function get_current_font_name()
{
	$arr = array(
		'raleway' 			=> 'Raleway',
		'volkorn' 			=> 'Volkorn',
		'oswald' 			=> 'Oswald',
		'nunito' 			=> 'Nunito',
		'arimo' 			=> 'Arimo',
		'open-sans' 		=> 'Open Sans',
		'ubuntu' 			=> 'Ubuntu',
		'arvo' 				=> 'Arvo',
		'exo' 				=> 'Exo',
		'roboto-condensed'	=> 'Roboto Condensed',
		'play' 				=> 'Play',
		'monsterrat' 		=> 'Monsterrat',
		'oxygen' 			=> 'Oxygen'
	);
	
	$custom_fontface_name = get_option('silicon_custom_fontface_name');
	
	return "'" . ($custom_fontface_name ? $custom_fontface_name : $arr[FONT]) . "'";
}


function silicon_wp_print_styles()
{
?>
<style type="text/css">

body, input[type="text"], input[type="password"], textarea, header #site_header #site_menu .main_menu li, .wpb_button, li, a, h4.wpb_toggle
{
	font-family: 'Ubuntu', sans-serif !important;
}

h1, h2, h3, h4, h5, .tp-caption.big_white {
	font-family: 'Pacifico', sans-serif !important;
}

</style>
<?php
}