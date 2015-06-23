<?php
// require_once( 'twitteroauth/autoload.php' );
// use Abraham\TwitterOAuth\TwitterOAuth;
//
// $keytoken = json_decode( file_get_contents( 'keytoken.json' ), true );
// $consumerKey        = $keytoken['consumerKey'];
// $consumerSecret     = $keytoken['consumerSecret'];
// $accessToken        = $keytoken['accessToken'];
// $accessTokenSecret  = $keytoken['accessTokenSecret'];

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
    function getRank()
    {
        // $sql = 'select l.title, l.url,d.screen_name, d.followers_count from anime_log d inner join anime_list l on l.screen_name=d.screen_name order by d.followers_count desc;';
        // 2015夏アニメ前評判 専用sql(日付指定)
        $sql = 'SELECT l.title, l.url,d.screen_name, d.followers_count
                FROM anime_log d
                INNER JOIN anime_list l ON l.screen_name=d.screen_name
                WHERE d.date >= "2015-06-22" AND d.date < "2015-06-23"
                ORDER BY d.followers_count DESC;';

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


// $a  = new Animerank( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
$db = new AnimerankDB();
// $lists = $db->getList();
//
// $i = 0;
// foreach( $lists as $title ){
//     // if( $i >= 3 ){
//     //     exit();
//     // }
//     // echo $title['screen_name'] . ":" . $a->getFollowersCount( $title['screen_name'] ) . "<br>";
//     $sn = $title['screen_name'];
//     if( !empty( $sn ) ){
//         $db->putData( $sn, $a->getFollowersCount( $sn ) );
//     }
//     $i = $i + 1;
//     echo $i;
// }

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>2015夏アニメ前評判ランキング</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54272662-1', 'auto');
  ga('send', 'pageview');

</script>
</head>
<body>
    <header>
        <div class="navbar navbar-default">
            <div class="container">
            <!--<div class="container-fluid">-->
                <a class="navbar-brand" href="#">Brand</a>
                <p class="navbar-text">2015夏アニメ前評判ランキング</p>
            </div>
        </div>
    </header>
    <div class="container">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- g_001 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-6018661257488318"
     data-ad-slot="3597685782"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
        <div class="row">
<?php
    $results = $db->getRank();

    // $html = '';
    // foreach( $results as $title ){
    //         $html .= '<div class="col-md-2">
    //             <div class="thumbnail">
    //                 <img src="http://lorempixel.com/200/200/" alt="" class="img-responsive">';
    //         $html .= '<h3>' . $title['title'] . '</h3>';
    //         $html .= '<p>'  . $title['followers_count'] . '</p>';
    //         $html .= '
    //             </div>
    //         </div>';
    // }
    // echo $html;
    $html = '<table class="table">';
    $html .= '<tr><th>順位</th><th>タイトル</th><th>twitterフォロワー数</th></tr>';
    $i = 0;
    foreach( $results as $title ){
        $html .= '<tr>'; 
        $html .= '<td>' . strval( $i + 1 ) . '</td>'; 
        $html .= '<td><a href="' . $title['url'] . '">' . $title['title']. '</a></td>'; 
        $html .= '<td>' . $title['followers_count']  . '</td>'; 
        $html .= '</tr>'; 
        $i = $i + 1;
    }
    $html .= '</table>';
    echo $html;
?>
        </div><!-- //.row -->
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
