<?php

require_once("vendor/simple_html_dom.php");
require_once("db-settings.php");
$argv[1] = 'populate';
if (!isset($argv[1])) {
    echo "usage: migrate | populate | wipe\n";
    exit;
}




// create db schema
if ($argv[1] == "migrate") {
    echo "Migrate database schema\n";
    shell_exec("mysql --user=" . $db['username'] . " --password=" . $db['password'] . " -h " . $db['hostname'] . " " . $db['database'] . " < " . $db['schema']);





// populate db with seed data
} elseif ($argv[1] == "seed") {
    echo "Populating DB with Seed-Data\n";
    shell_exec("mysql --user=" . $db['username'] . " --password=" . $db['password'] . " -h " . $db['hostname'] . " " . $db['database'] . " < " . $db['seed']);


// populate db with live data
} elseif ($argv[1] == "populate") {

    echo "Populating DB with Live-Data\n";
    // connect to db
    /*
    $connection = mysql_connect($db['hostname'], $db['username'], $db['password']);
    if (!$connection) {
        die('connection failure: ' . mysql_error() . "\n");
    }
    // select table
    mysql_select_db($db['database']) or die("Unable to select database\n");
    */
    $con = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to Mysql: " . $mysqli_connect_errno();
    }
    mysqli_select_db($con, $db['database']);


    // insert pulling data from api
    // Dow Jones Industrial Index Pulling
    // $xml = simplexml_load_file('http://www.google.com/ig/api?stock=.DJI');
    // if($xml ===  FALSE) {
    //	echo "Something wrong with Google Api\n";
    //mail('webmaster@appropo.net', 'golding capital partners', 'Google Api error');
    //} 
    //$finance = $xml->xpath("/xml_api_reply/finance");
    //$name = $finance[0]->symbol['data'];
    //$last_Data = $finance[0]->last['data'];
    //mysql_query("INSERT INTO stock (stock, value) VALUES ('".$name."', ".$last_Data.")");
    // Dax, ftse & nikkei
    // http://developer.yahoo.com/yql/console/?q=select%20*%20from%20yahoo.finance.quotes%20where%20symbol%20in%20(%22%5EGDAXI%22%2C%20%22%5EFTSE%22%2C%20%22%5EN225%22)%0A%09%09&env=http%3A%2F%2Fdatatables.org%2Falltables.env
    // Create a DOM object
    $html = new simple_html_dom();

    // Load HTML from a URL 
    $html->load_file('http://www.google.com/finance?cid=983582');

    $name = '.DJI';

    $last_Data = $html->find('#ref_983582_l', 0)->innertext;
    //$last_Data = (float) str_replace(',', '', $last_Data);
	$last_Data = str_replace(',', '', $last_Data);

    mysqli_query($con, "INSERT INTO stock (stock, value) VALUES ('" . $name . "', " . $last_Data . ")");

    $xml_yql = simplexml_load_file('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.quotes%20where%20symbol%20in%20(%22%5EGDAXI%22%2C%20%22%5ETECDAX%22%2C%20%22%5EFTSE%22%2C%20%22%5EN225%22)%0A%09%09&env=http%3A%2F%2Fdatatables.org%2Falltables.env');
    if ($xml_yql === FALSE) {
        echo "Something wrong with Yahoo finance Api\n";
        //mail('webmaster@appropo.net', 'golding capital partners', 'Yahoo finanace Api error');
    }
    $results = $xml_yql->results;
    foreach ($results->quote as $quote) {
        $name = $quote->Name;
        $last_Data = $quote->LastTradePriceOnly;
        mysqli_query($con, "INSERT INTO stock (stock, value) VALUES ('" . $name . "', " . $last_Data . ")");
    }

    // Weather
    // yql
    //http://developer.yahoo.com/yql/console/?q=select%20*%20from%20rss%20where%20url%20in%20(%22http%3A%2F%2Fweather.yahooapis.com%2Fforecastrss%3Fw%3D2459115%26u%3Dc%22%2C%22http%3A%2F%2Fweather.yahooapis.com%2Fforecastrss%3Fw%3D676757%26u%3Dc%22%2C%22http%3A%2F%2Fweather.yahooapis.com%2Fforecastrss%3Fw%3D44418%26u%3Dc%22%2C%22http%3A%2F%2Fweather.yahooapis.com%2Fforecastrss%3Fw%3D1118370%26u%3Dc%22)&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys



/* insert weather */
$json = file_get_contents('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22frankfurt%2C%20fr%22%20or%20text%3D%22new%20york%2C%20ny%22%20or%20text%3D%22london%2C%20ln%22%20or%20text%3D%22tokyo%2C%20tyo%22%20or%20text%3D%22munich%2C%20agb%22)%20and%20u=%27c%27%20&format=json&u=c&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys');
$result = json_decode($json);

$time = date("Y-m-d H:i:s");
foreach ($result->query->results->channel as $val) {
    $location = $val->location->city;
    $temp = $val->item->condition->temp;

    if ($location != '') {
        $sql = "INSERT INTO weather (city, value, time) VALUES ('" . $location . "', '" . $temp ."', '" . $time . "')";
        $ins = mysqli_query($con, $sql);
    }                               
}
    // example
    //mysql_query("INSERT INTO weather (city, value) VALUES ('Muc', 1.98)");
    //mysql_query("INSERT INTO stock (stock, value) VALUES ('Dax', 7703.32)");
    // close connection
    mysqli_close($con);





// wipe database
} elseif ($argv[1] == "wipe") {

    echo "Wiping Database\n";
    $con = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to Mysql: " . $mysqli_connect_errno();
    }
    mysqli_select_db($con, $db['database']);

    $sql = "TRUNCATE TABLE weather";
    mysql_query($sql);

    $sql = "TRUNCATE TABLE stock";
    mysqli_query($sql);

    mysqli_close($connection);



// no argument given
} else {
    echo "usage: migrate | populate | wipe\n";
}
?>
