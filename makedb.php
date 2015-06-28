<?php
require_once( 'twitteroauth/autoload.php' );
use Abraham\TwitterOAuth\TwitterOAuth;

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

class AnimerankDB
{
    private $db;

    function __construct()
    {
        // $dbname = 'sqlite:/var/www/animerank/2015summer/animerank.db';
        $dbname = 'sqlite:' . __DIR__ . '/animerank.db';

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
        }catch( PDOException $e ){
            die( 'getList error' . $e->getMessage() );
        }

        return $results;
    }
    function getRank()
    {
        $sql = 'select l.title, l.url,d.screen_name, d.followers_count from anime_log d inner join anime_list l on l.screen_name=d.screen_name order by d.followers_count desc;';

        try{
            $stmt = $this->db->query( $sql );
            $results = $stmt->fetchAll( PDO::FETCH_ASSOC );
        }catch( PDOException $e ){
            die( 'getList error' . $e->getMessage() );
        }

        return $results;
    }
    function putData( $screen_name, $followers_count )
    {
        // 同じ日の重複挿入を避ける
        $sql = 'SELECT screen_name FROM anime_log
                where date>=:date_start AND date<:date_end AND screen_name=:screen_name';
        $stmt = $this->db->prepare( $sql );
        $stmt->bindValue( ':date_start', date( 'Y-m-d' ), PDO::PARAM_STR );
        $stmt->bindValue( ':date_end',   date( 'Y-m-d', strtotime( '+1 day' ) ), PDO::PARAM_STR );
        $stmt->bindValue( ':screen_name', $screen_name, PDO::PARAM_STR );
        try{
            $stmt->execute();
            $results = $stmt->fetchAll( PDO::FETCH_ASSOC );
        }catch( PDOException $e ){
            die( 'putData error in SELECT: ' . $e->getMessage() );
        }
        if( !empty( $results ) ){
            return;
        }

        // データ挿入
        $sql = 'INSERT INTO anime_log VALUES( :screen_name, :date, :followers_count)';
        $stmt = $this->db->prepare( $sql );
        $stmt->bindValue( ':screen_name', $screen_name, PDO::PARAM_STR );
        $stmt->bindValue( ':date', date( 'Y-m-d H:i:s' ), PDO::PARAM_STR );
        $stmt->bindValue( ':followers_count', $followers_count, PDO::PARAM_INT );
        
        try{
            $stmt->execute();
        }catch( PDOException $e ){
            die( 'putData error in INSERT INTO: ' . $e->getMessage() );
        }
    }
}


$a  = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
$db = new AnimerankDB();
$lists = $db->getList();

$i = 0;
foreach( $lists as $title ){
    // if( $i >= 2 ){  // for test
    //     exit();
    // }
    $sn = $title['screen_name'];
    if( empty( $sn ) ) continue;
    $fc = $a->getFollowersCount( $sn );
    echo strval( $i + 1 ) . " " . $sn . "<br>\n";
    if( !empty( $sn ) ){
        $db->putData( $sn, $fc );
        echo strval( $i + 1 ) . " " . $sn . " " . $fc . "<br>\n";
    }
    $i = $i + 1;
}
