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

// $connection = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );

// $api = 'users/show';
// $content = $connection->get( $api, array(
//     'screen_name' => 'akkagi0416',
// ) );

// echo $content->{'statuses_count'};  // ツイート数
// echo $content->{'friends_count'};   // フォロー数
// echo $content->{'followers_count'}; // フォロワー数
// var_dump( $content );

class Animerank
{
    private $connection;

    function __construct( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret )
    {
        $this->connection = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
    }
    function getFollowersCount( $screen_name )
    {
        $api = 'users/show';
        $userinfo = $this->connection->get( $api, array(
            'screen_name' => 'akkagi0416',
        ) );
        // var_dump( $userinfo );
        return $userinfo->{'followers_count'}; 
    }
}

$a = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
echo $a->getFollowersCount( 'akkagi0416' );
