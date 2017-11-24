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

$task = 'add';
if (isset($_GET['task'])) {
    $task = $_GET['task'];
}

$con = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);
if (mysqli_connect_errno()) {
    echo "Failed to connect to Mysql: " . $mysqli_connect_errno();
}
mysqli_select_db($con, $db['database']);

if ($task == 'add') {
    if ((int)date("His") >= 90000) {
        addStock($con);
    }
}

function addStock($con) {
    $code = array(
        array(
            'name' => '.DJI',
            'url' => 'https://www.google.com/finance?cid=983582',
            'id' => '#ref_983582_l'
        ),
        array(
            'name' => 'DAX',
            'url' => 'https://www.google.com/finance?q=DAX&ei=TZ-qWKHuOcLDswH8tbOQCQ',
            'id' => '#ref_14199910_l'
        ),
        array(
            'name' => 'FTSE 100',
            'url' => 'https://www.google.com/finance?q=FTSE+100&ei=lJ-qWICIJ5iLswGFqp7IBg',
            'id' => '#ref_12590587_l'
        ),
        array(
            'name' => 'Nikkei 225',
            'url' => 'https://www.google.com/finance?q=Nikkei+225&ei=f76qWMnzJZmCsQGKrZbABw',
            'id' => '#ref_15513676_l'
        ),
        array(
            'name' => 'TECDAX',
            'url' => 'https://www.google.com/finance?q=TECDAX&ei=vb6qWIDQD5KDsAHepov4Cw',
            'id' => '#ref_5301320_l'
        )
    );

    foreach ($code as $val) {
        $name = $val['name'];
        $url = $val['url'];
        $id = strip_tags($val['id']);
        $html = new simple_html_dom();
        $html->load_file($url);
        $last_Data = $html->find($id, 0)->innertext;
        $last_Data = (float) str_replace(',', '', $last_Data);

        mysqli_query($con, "INSERT INTO stock (stock, value) VALUES ('" . $name . "', " . $last_Data . ")");
    }
}


mysqli_close($con);
die('Done');
