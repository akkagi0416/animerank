<?php

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

    function getRank2()
    {
        $result1 = $this->getData( "2015-06-22" );
        $result2 = $this->getData( date( "Y-m-d" ) );

        if( count( $result1 ) != count( $result2 ) ){
            die( 'error: database array size different' );
        }
        // フォロワー数の増加量計算
        for( $i = 0; $i < count( $result1 ); $i++ ){
            $increment = $result2[$i]['followers_count'] - $result1[$i]['followers_count'];
            $result2[$i]['followers_count'] = $increment;
        }
        // フォロワー数の多い順にソート
        $followers_count = array();
        foreach( $result2 as $v ) $followers_count[] = $v['followers_count'];
        array_multisort( $followers_count, SORT_DESC, SORT_NUMERIC, $result2 );

        return $result2;
    }

    private function getData( $str_date )
    {
        $sql = 'SELECT l.title, l.url,d.screen_name, d.followers_count
                FROM anime_log d
                INNER JOIN anime_list l ON l.screen_name=d.screen_name
                WHERE d.date >= :date_start AND d.date < :date_end
                ORDER BY l.id ASC;';

        $stmt = $this->db->prepare( $sql );
        $stmt->bindValue( ':date_start', $str_date, PDO::PARAM_STR );
        $stmt->bindValue( ':date_end'  , date( "Y-m-d", strtotime( $str_date . " +1 days" ) ), PDO::PARAM_STR );
        try{
            $stmt->execute();
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

$db = new AnimerankDB();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>2015夏アニメ前評判ランキング(増加数版)</title>
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
                <p class="navbar-text">2015夏アニメ前評判ランキング(増加数版) 06/23 ～ <?php echo date( 'm/d' ); ?>まで</p>
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
    // $results = $db->getRank();
    $results = $db->getRank2();
    
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
