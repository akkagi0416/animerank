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
            'screen_name' => $screen_name,
        ) );
        // var_dump( $userinfo );
        return $userinfo->{'followers_count'}; 
    }
}

/*
 * Mvnoのデータベースを扱う
 * $m = new Mvno();
 * $results = $m->getInfo( 'dmm' );
 * $results['shortname'] -> 'dmm';
 */
class AnimerankDB
{
    private $db;

    function __construct()
    {
        // $dbname = 'sqlite:/var/www/animerank/2015summer/animerank.db';
        $dbname = 'sqlite:animerank.db';

        try{
            $this->db = new PDO( $dbname );
            $this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }catch( PDOException $e ){
            die( 'DB connect error:' . $e->getMessage() );
        }
    }

    function __destruct()
    {
        $this->db = null;
    }

    function getList()
    {
        $sql = 'SELECT screen_name FROM anime_list';

        try{
            $stmt = $this->db->query( $sql );
            $results = $stmt->fetchAll( PDO::FETCH_ASSOC );
            // $results = $stmt->fetchAll( PDO::FETCH_ASSOC );
        }catch( PDOException $e ){
            die( 'getInfo error' . $e->getMessage() );
        }

        return $results;
    }
    function getPlan( $shortname )
    {
        $sql = 'SELECT * FROM mvno_plan WHERE shortname = :shortname ORDER BY id_plan ASC';
        $stmt = $this->db->prepare( $sql );
        $stmt->bindValue( ':shortname', $shortname, PDO::PARAM_STR );
        
        try{
            $stmt->execute();
            // $result = $stmt->fetch( PDO::FETCH_ASSOC );
            $results = $stmt->fetchAll( PDO::FETCH_ASSOC );
        }catch( PDOException $e ){
            die( 'getInfo error' . $e->getMessage() );
        }
        
        // return $result;
        return $results;
    }
}

// $a = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
// echo $a->getFollowersCount( 'akkagi0416' );

// $list = ['akkagi0416', 'anime_okusama'];
// foreach( $list as $title ){
//     echo $title . "\t" . $a->getFollowersCount( $title ) . "\n";
// }

$a  = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
$db = new AnimerankDB();
$lists = $db->getList();

// var_dump( $list );
$i = 0;
foreach( $lists as $title ){
    if( $i >= 3 ){
        exit();
    }
    echo $title['screen_name'] . ":" . $a->getFollowersCount( $title['screen_name'] ) . "<br>";
    $i = $i + 1;
}
