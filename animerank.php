<?php
require_once( 'twitteroauth/autoload.php' );
use Abraham\TwitterOAuth\TwitterOAuth;

// $consumerKey        = '';
// $consumerSecret     = '';
// $accessToken        = '';
// $accessTokenSecret  = '';
$keytoken = json_decode( file_get_contents( 'keytoken.json' ), true );
$consumerKey        = $keytoken['consumerKey'];
$consumerSecret     = $keytoken['consumerSecret'];
$accessToken        = $keytoken['accessToken'];
$accessTokenSecret  = $keytoken['accessTokenSecret'];

$connection = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );

$api = 'users/show';
$content = $connection->get( $api, array(
    'screen_name' => 'akkagi0416',
    ) );

var_dump( $content );
