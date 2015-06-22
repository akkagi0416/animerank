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
        }catch( PDOException $e ){
            die( 'getList error' . $e->getMessage() );
        }

        return $results;
    }
    function putData( $screen_name, $followers_count )
    {
        $sql = 'INSERT INTO anime_log VALUES( :screen_name, :date, :followers_count)';
        $stmt = $this->db->prepare( $sql );
        $stmt->bindValue( ':screen_name', $screen_name, PDO::PARAM_STR );
        $stmt->bindValue( ':date', date( 'Y-m-d H:i:s' ), PDO::PARAM_STR );
        $stmt->bindValue( ':followers_count', $followers_count, PDO::PARAM_INT );

        try{
            $stmt->execute();
        }catch( PDOException $e ){
            die( 'putData error' . $e->getMessage() );
        }
    }
}


$a  = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
$db = new AnimerankDB();
$lists = $db->getList();

$i = 0;
foreach( $lists as $title ){
    // if( $i >= 3 ){
    //     exit();
    // }
    // echo $title['screen_name'] . ":" . $a->getFollowersCount( $title['screen_name'] ) . "<br>";
    $sn = $title['screen_name'];
    if( !empty( $sn ) ){
        $db->putData( $sn, $a->getFollowersCount( $sn ) );
    }
    $i = $i + 1;
    echo $i;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>2015夏アニメ前評判ランキング</title>
</head>
<body>
    
</body>
</html>
