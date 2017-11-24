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

$con = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
if (mysqli_connect_errno()) {
    echo "Failed to connect to Mysql: " . $mysqli_connect_errno();
}
mysqli_select_db($con, $db['database']);

/**/
$today = date("Y-m-d H:i:s");
$last_week = strtotime("-1 week +1 day");
$d_date = date("Y-m-d H:i:s", $last_week);

$del_stock = "DELETE FROM stock WHERE time < '".$d_date."'";
$del_weather = "DELETE FROM weather WHERE time < '".$d_date."'";
mysqli_query($con, $del_stock);
mysqli_query($con, $del_weather);

mysqli_close($con);
die('Done');
