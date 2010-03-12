<?php
/*
Plugin Name: Jorbin Shortcodes
Plugin URI: http://aaron.jorb.in/
Description: Shortcodes I use
Author: Aaron Jorbin
Version: 0.0
Author URI: http://aaron.jorb.in/
License: GPL2
*/
function jorbin_twitter_trends(){

	$transient='twitter-trends';
	$url = 'http://search.twitter.com/trends.json';

	if ( $tweet_display = get_transient($transient) ){

	}
	else{
		$search = wp_remote_get( $url );

		$results = json_decode($search['body']);
		$trends = $results->trends;
		ob_start();
			echo "<ul class='twitter-trends'>";
			foreach ($trends as $trend){
				echo '<li><a href="' . esc_url($trend->url) . '"> '. esc_html($trend->name) . '</a></li>';
			}
			echo "</ul>";
		$tweet_display = ob_get_clean();
		set_transient($transient, $tweet_display, 120);
	}
	return $tweet_display;
}

add_shortcode('twitter-trends', 'jorbin_twitter_trends');

function jorbin_firestream_search($atts){
	extract(shortcode_atts(array(
	'phrase' => false,
	'lang' => 'en',
	'max_id' => false,
	'since_id' => false,
	'number' => '20'
	), $atts));
	if ('phrase' == false){
		return false;
	}
	//*/ Build our search url and transient name
	$transient = 'tweet-'. esc_sql($phrase) . '&l=' . esc_sql($lang);
	$url = 'http://search.twitter.com/search.json?q='. urlencode($phrase) . '&show_user=true&lang='. urlencode($lang) .'&rpp=' . $number;

	if ($max_id != false){
		$url .= '&max_id=' . (int) $max_id;
		$transient .= '&m=' . (int) $max_id;
	}
	if ($since_id != false){
		$url .= '&since_id=' . (int) $since_id;
		$transient .= '&s=' . (int) $since_id;
	}
	var_dump($transient);

	if ( $tweet_display = get_transient($transient) ){
		// It's allready been brought
	}
	else {

		$search = wp_remote_get( $url );
		$results = json_decode($search['body']);

		ob_start();
			$tweets = $results->results;
			 //*/
			foreach ( (array) $tweets as $tweet){
				$tweetcontent = $tweet->text;
				$newcontent = preg_replace('%@([^\s]*)%', "<a href='http://twitter.com/\\1' >@\\1</a>", $tweetcontent);
				echo "<div class='twitter_shortcode' ><p>
				<img class='twitter_shortcode_image' src='".esc_url($tweet->profile_image_url)."' class='twitter_shortcode_image'  /><span class='twitter_shotcode_username'><a href='http://twitter.com/".$tweet->from_user."' >".$tweet->from_user."</a>&nbsp;&mdash;&nbsp;</span>$newcontent</p>
				</div>";

			}
		$tweet_display = ob_get_clean();
		set_transient($transient, $tweet_display, 120);
		}
	return $tweet_display;
}

add_shortcode('twitter-search', 'jorbin_firestream_search');

//Allow text widgets to use shortcodes
add_filter('widget_text', 'do_shortcode');

// [twitter_status screenname='' count='']

function twitter_status($atts){
	extract(shortcode_atts(array(
	'screenname' => '',
	'count' => 1
	), $atts));
	$transient = "$screenname"."_$count"."_twitter_status";
	$statuses =  get_transient($transient);
	if ($statuses == true  )
	{
		return $statuses;
	}
	elseif ($screenname != false)
	{
		$site = "http://twitter.com/statuses/user_timeline.json?screen_name=$screenname&count=$count";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $site);
		$result = curl_exec($ch);
		$tweets = json_decode($result);
		ob_start();
		foreach ( (array) $tweets as $tweet){
			$tweetcontent = $tweet->text;
			$newcontent = preg_replace('%@([^\s]*)%', "<a href='http://twitter.com/\\1' >@\\1</a>", $tweetcontent);
			echo "<div class='twitter_shortcode' ><p>
			<img class='twitter_shortcode_image' src='".esc_url($tweet->user->profile_image_url)."' class='twitter_shortcode_image'  /><span class='twitter_shotcode_username'><a href='http://twitter.com/".$tweet->user->screen_name."' >".$tweet->user->screen_name."</a>&nbsp;&mdash;&nbsp;</span>$newcontent</p>
			</div>";

		}
		$tweet_display = ob_get_clean();
		set_transient($transient, $tweet_display, 120);
		return $tweet_display;
	}
	else
	{
		return false;
	}
}

add_shortcode('twitter_status', 'twitter_status');
// end twitter_status shortcode

// [show_bookmark_image_list catagory_name='']
function show_bookmark_image_list($atts){
	extract(shortcode_atts(array(
	'catagory_name' => false
	), $atts));
	if ( $catagory_name == false )
		$bookmarks = get_bookmarks();
	else
		$bookmarks = get_bookmarks("catagory_name=$catagory_name");
	ob_start();
	echo "<ul class='link-image-list'>";
	foreach($bookmarks as $bookmark){
		echo "<li><a href='".esc_url($bookmark->link_url) ."'><img src='".esc_url($bookmark->link_image)."' /></a></li>";
	}
	echo "</li>";
	$list = ob_get_clean();
	return $list;
}
add_shortcode('show_bookmark_image_list', 'show_bookmark_image_list');
//end show_bookmark_image_list

// [show_current_year]
function show_current_year(){
	return date('Y');
}
add_shortcode('show_current_year', 'show_current_year');
// end show_current_year
?>