<?php
/**
* TWITTER API v.1.1
 * 
 * Get latest tweet via Twitter API, in JSON format,
 * using [https://github.com/abraham/twitteroauth] by @abraham
 * Format tweet with working URLs for urls, hashtags, and mentions
 * 
 * @param $user
 * @param $tweet_count
 */

	require_once('twitteroauth/tweets.php');

	function display_latest_tweets( $user, $tweets_to_show ) {
		// Client input requirements
		$twitter_user_id = $user;
		$amount_tweets = $tweets_to_show;
		// Absolute file path required for fopen(), fwrite(), and fclose() later
		$cache_file = ABSPATH.'wp-content/themes/'.WP_THEME_FOLDER.'/twitter/tweets.json';
		// Cache time default: 180 seconds (3 minutes)
		$cachetime = 180;
		$ignore_replies = false;
		$include_rts = true;
		$date_format = 'g:i A M jS';
		$twitter_style_dates = true;
		$allow_replies = true;
		$allow_retweets = true;
		$allow_faves = true;

		$twitter_wrap_open		= '<section><ul>';
		$tweet_wrap_open		= '<li class="tweet">';
		$tweet_wrap_close		= '</li>';
		$twitter_wrap_close		= '</ul></section>';

		// Time that the cache was last updtaed.
		$cache_file_created = ( ( file_exists( $cache_file ) ) ) ? filemtime( $cache_file ) : 0;
		// A flag so we know if the feed was successfully parsed.
		$tweet_found = false;

		// Twitter keys (You'lll need to visit https://dev.twitter.com/apps and register to get these.
		$consumerkey		= "xxxxxxxxxxxxxxxxxxxxxx";
		$consumersecret		= "xxxxxxxxxxxxxxxxxxxxxx";
		$accesstoken		= "xxxxxxxxxxxxxxxxxxxxxx";
		$accesstokensecret	= "xxxxxxxxxxxxxxxxxxxxxx";

		// Show cached version of tweets,
		// if cache_file exists, cache_file has content, and was created less than $cachetime ago.
		if ( file_exists( $cache_file ) && 
			( filesize( $cache_file ) > 0 ) && 
			( time() - $cachetime < $cache_file_created ) ) {

			$tweet_found = true;
			// Get cached tweets.
			// Cache file stored as JSON for easier control
			$get_tweets = json_decode( file_get_contents( $cache_file ) );
			// Use this to in Dev mode to view JSON output
			//echo '<pre>'; /*print_r($get_tweets);*/ echo '</pre>';
		} else {
			// Cache file not found, or old.
			// Authenticate app.
			$connection = getConnectionWithAccessToken( $consumerkey, $consumersecret, $accesstoken, $accesstokensecret );

			if( $connection ){
				// Get the latest tweets from Twitter
				$get_tweets = $connection->get( "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_user_id."&count=".$amount_tweets."&include_rts=".$include_rts);
				// Use this to in Dev mode to view JSON output
				//echo '<pre>'; print_r($get_tweets); echo '</pre>';
			}
		}

		// Optional. Depending on required HTML markup.
		?>
		<header>
			<h3><a href="https://twitter.com/<?=$twitter_user_id;?>" target="_blank" title="<?=$get_tweets[0]->user->description;?>">@<?=$twitter_user_id;?></a></h3>
		</header>
		<?php

		// Error check: Make sure there is at least one item.
		if ( count( $get_tweets ) && ( count( $get_tweets->errors ) <= 0 ) ) {
			// Define tweet_count as zero
			$tweet_count = 0;

			// Iterate over tweets.
			foreach( $get_tweets as $tweet ) {
				/*
				 * USEFUL REFERENCES FOR MOST USED LINKS
				 * 
				 * Follow:			https://twitter.com/intent/user?screen_name=$tweets->screen_name
				 * Reply:			https://twitter.com/intent/tweet?in_reply_to=$tweets->id_str
				 * Retweet:			https://twitter.com/intent/retweet?tweet_id=$tweets->id_str
				 * Favourite:		https://twitter.com/intent/favorite?tweet_id=$tweets->id_str
				 * Timestamp:		$get_tweets[0]->user->created_at
				 * Avatar:			$get_tweets[0]->user->profile_image_url_https
				 * Username:		$get_tweets[0]->user->screen_name
				 * Display Name:	$get_tweets[0]->user->name
				 */
				// If we are not ignoring replies, or tweet is not a reply, process it.
				if ( $ignore_replies == false ) {
					$tweet_found = true;
					$tweet_count++;

					$tweet_desc = $tweet->text;
					// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
					// URLs must be validated first
					$tweet_desc = preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="$1" target="_blank">$1</a>',$tweet_desc);
					$tweet_desc = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2" target="_blank">@$2</a>', $tweet_desc);
					$tweet_desc = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2" target="_blank">#$2</a>', $tweet_desc);

					echo $tweet_wrap_open.html_entity_decode($tweet_desc).$tweet_wrap_close;

					// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
					$tweet_time = strtotime($tweet->created_at);
					if ( $twitter_style_dates ) {
						$current_time = time(); // Current UNIX timestamp.
						$time_diff = abs( $current_time - $tweet_time );
						switch ( $time_diff ) {
							case ( $time_diff < 60 ):
								$display_time = $time_diff.' seconds ago';
								break;
							case ( $time_diff >= 60 && $time_diff < 3600 ):
								$min = floor( $time_diff / 60 );
								$display_time = $min.' minutes ago';
								break;
							case ( $time_diff >= 3600 && $time_diff < 86400 ):
								$hour = floor( $time_diff / 3600 );
								$display_time = 'about '.$hour.' hour';
								if ( $hour > 1 ) { $display_time .= 's'; }
								$display_time .= ' ago';
								break;
							default:
								$display_time = date( $date_format, $tweet_time );
								break;
						}
					} else {
						$display_time = date( $date_format, $tweet_time );
					}

					$meta_links = '<footer><div class="options"><img alt="" class="icon icoTweet" src="'.get_bloginfo('template_url').'/img/icoTweet.svg" />';
					$meta_links .= ($allow_replies) ? '<a href="https://twitter.com/intent/tweet?in_reply_to='.$tweet->id_str.'" class="_option tw--reply">Reply</a>' : null;
					$meta_links .= ($allow_retweets) ? '<a href="https://twitter.com/intent/retweet?tweet_id='.$tweet->id_str.'" class="_option tw--rt">Retweet</a>' : null;
					$meta_links .= ($allow_faves) ? '<a href="https://twitter.com/intent/favorite?tweet_id='.$tweet->id_str.'" class="_option tw--fav">Favourite</a>' : null;
					$meta_links .= '<a class="_option tw--datetime" href="https://twitter.com/'.$get_tweets[0]->user->screen_name.'/status/'.$tweet->id_str.'">'.$display_time.'</a>';
					$meta_links .= '</div></footer>';

					echo $meta_links;
				}

				// If we have processed enough tweets, stop.
				if ($tweet_count >= $tweets_to_display) break;
			}
		} else {
			// No tweets can be found.
			// Twitter may be down.
			echo $twitter_wrap_open.$tweet_wrap_open.'No tweets can be found. Twitter may be down.'.$tweet_wrap_close.$twitter_wrap_close;
		}

		// Generate a new cache file.
		$file = fopen($cache_file, 'w');

		// Save the contents of the JSON call to the file
		// Cache file contents stored as JSON for easier, standardised control throughout
		fwrite($file, json_encode($get_tweets));
		fclose($file);
	}
?>