<?php
include 'parameter.php';
if (!DB_connect($GLOBALS["dbhost"], $GLOBALS["dbUser"], $GLOBALS["dbPassword"], $GLOBALS["dbname"])) {
    $data['errorText'] = "can't connect to database!";
    jsonAnswer($data);
}

function jsonAnswer($data)
{
    $data = (is_array($data)) ? json_encode($data) : $data;
//    header('Content-Type: text/json; charset=utf-8');
    echo $data;
    exit();
}

/**
 * creates DB connection
 * @param $host
 * @param $user
 * @param $pass
 * @param $dbName
 * @return bool
 */
function DB_connect($host, $user, $pass, $dbName)
{
    $_connect = mysql_pconnect($host, $user, $pass);
    $_select = mysql_select_db($dbName, $_connect);
    if (!($_connect && $_select)) return false;
    mysql_query("set character_set_client='utf8';");
    mysql_query("set character_set_results='utf8';");
    mysql_query("set collation_connection='utf8';");
    return true;
}

/**
 * @param $message
 */
function debug($message)
{
    global $debug;
    if ($GLOBALS["debug"]) {
        echo "
        <span style='color:red'>$message</span>
        ";
    }
}

/**
 * @param $array
 * @param $draws
 * @return array
 */
function drawRandArray($array, $draws)
{
    $lastIndex = count($array) - 1;
    $returnArr = array();
    while ($draws > 1) {
        $rndIndex = rand(0, $lastIndex);
        array_push($returnArr, array_splice($array, $rndIndex, 1));
        $draws--;
        $lastIndex--;
    }
    return $returnArr;
}


?>
