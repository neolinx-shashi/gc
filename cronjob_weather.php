<?php

require_once("vendor/simple_html_dom.php");
require_once("db-settings.php");

$token = 'AXJKJ9890SDS';
if (isset($_GET['token'])) {
    $gettoken = $_GET['token'];
    if ($token != $gettoken) {
        die;
    }
} else {
    die;
}

$city = array(
        'Munich' => 'Europe/Berlin',
        'Tokyo' => 'Asia/Tokyo',
        'London' => 'Europe/London',
        'New York' => 'America/New_York',
        'Frankfurt' => 'Europe/Berlin'
    );

$con = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
if (mysqli_connect_errno()) {
    echo "Failed to connect to Mysql: " . $mysqli_connect_errno();
}
mysqli_select_db($con, $db['database']);

$json = file_get_contents('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22frankfurt%2C%20fr%22%20or%20text%3D%22new%20york%2C%20ny%22%20or%20text%3D%22london%2C%20ln%22%20or%20text%3D%22tokyo%2C%20tyo%22%20or%20text%3D%22munich%2C%20agb%22)%20and%20u=%27c%27%20&format=json&u=c&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys');
$result = json_decode($json);

//$time = date("Y-m-d H:i:s");
foreach ($result->query->results->channel as $val) {
    $location = $val->location->city;
    $temp = $val->item->condition->temp;

    date_default_timezone_set($city[$location]);
    $time = date("Y-m-d H:i:s");

    $sql = "INSERT INTO weather (city, value, time) VALUES ('" . $location . "', '" . $temp . "', '" . $time . "')";
    $ins = mysqli_query($con, $sql);
}
mysqli_close($con);
die('Done');