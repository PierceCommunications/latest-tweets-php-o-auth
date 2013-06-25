<?php
/**
 * Twitter feed which uses twitteroauth for authentication
 * 
 * @version	1.0
 * @author	Andrew Biggart
 * @link	https://github.com/andrewbiggart/latest-tweets-php-o-auth/
 * 
 * Notes:
 * Caching is employed because Twitter only allows their RSS and json feeds to be accesssed 150
 * times an hour per user client.
 * --
 * Dates can be displayed in Twitter style (e.g. "1 hour ago") by setting the 
 * $twitter_style_dates param to true.
 *
 * You will also need to register your application with Twitter, to get your keys and tokens.
 * You can do this here: (https://dev.twitter.com/).
 *
 * Don't forget to add your username to the bottom of the script.
 * 
 * Credits:
 ***************************************************************************************
 * Initial script before API v1.0 was retired
 * http://f6design.com/journal/2010/10/07/display-recent-twitter-tweets-using-php/
 *
 * Which includes the following credits
 * Hashtag/username parsing based on: http://snipplr.com/view/16221/get-twitter-tweets/
 * Feed caching: http://www.addedbytes.com/articles/caching-output-in-php/
 * Feed parsing: http://boagworld.com/forum/comments.php?DiscussionID=4639
 ***************************************************************************************
 *
 ***************************************************************************************
 * Authenticating a User Timeline for Twitter OAuth API V1.1
 * http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/
 ***************************************************************************************
 *
 ***************************************************************************************
 * Twitteroauth which has been used for the authentication process
 * https://github.com/abraham/twitteroauth
 ***************************************************************************************
 *
 *
**/
 
// Session start
session_start();

// Set timezone. (Modify to match your timezone)
// If you need help with this, you can find it here. (http://php.net/manual/en/timezones.php)
date_default_timezone_set('Europe/London');

// Require TwitterOAuth files. (Downloadable from : https://github.com/abraham/twitteroauth)
require_once("twitteroauth/twitteroauth/twitteroauth.php");

// Function to authenticate app with Twitter.
function getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret) {
	$connection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
	return $connection;
}
?>