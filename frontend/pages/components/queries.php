<?php
function dbConnect() {

    // Open DB Connection
    $host = 'localhost';
    $database = 'ParadiseCoffee';
    $username = getenv('POSTGRE_USER');
    $password = getenv('POSTGRE_PASS');
    $db = pg_connect("host=$host dbname=$database user=$username password=$password");
    if (!$db) {
        echo "Failed to connect to PostgreSQL database.";
        exit;
    }
    return $db;
}
function getUserByToken($token) {
    $currenttime = time();
    $db = dbConnect();
    $query = "SELECT * FROM authtoken WHERE expiry > $currenttime AND tokenid = '$token'";
    $result = pg_query($db, $query);
    if (!$result) {
        echo "Error executing the query.";
        exit;
    }
    while ($row = pg_fetch_assoc($result)) {
        $tokenid = $token;
        return true;
    }
    pg_free_result($result);
    pg_close($db);
}
function checkAuthentication() {
    if (isset($_COOKIE['user_token'])) {
        $token = $_COOKIE['user_token'];
        $auth = getUserByToken($token);
        if ($auth) {
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}
function getYesterdayTotal() {

    if (!$authorise = checkAuthentication()) {
        return false;
    }

    $date = date('Y-m-d', strtotime('-1 day'));
    $db = dbConnect();
    $query = "SELECT * FROM daily WHERE day = '$date'";
    $result = pg_query($db, $query);
    if (!$result) {
        echo "Error executing the query.";
        exit;
    }
    while ($row = pg_fetch_assoc($result)) {
        $total = $row['cash'] + $row['eftpos'];
        return $total;
    }
    pg_free_result($result);
    pg_close($db);
}

function getTodayTotal() {

    if (!$authorise = checkAuthentication()) {
        return false;
    }

    $date = date('Y-m-d');
    $db = dbConnect();
    $query = "SELECT * FROM daily WHERE day = '$date'";
    $result = pg_query($db, $query);
    if (!$result) {
        return 0;
    }
    while ($row = pg_fetch_assoc($result)) {
        return $row;
    }
    pg_free_result($result);
    pg_close($db);
}
function getPreviousOrders() {

    if (!$authorise = checkAuthentication()) {
        return false;
    }

    $date = date('Y-m-d');
    $db = dbConnect();
    $query = "SELECT * FROM daily WHERE day = '$date'";
    $result = pg_query($db, $query);
    if (!$result) {
        return 0;
    }
    while ($row = pg_fetch_assoc($result)) {
        return $row;
    }
    pg_free_result($result);
    pg_close($db);
}
function insertCash($cash) {

    if (!$authorise = checkAuthentication()) {
        return false;
    }

    $date = date('Y-m-d');
    $db = dbConnect();
    $insert = true;
    $eftpos = 0;

    // Check for an existing record for today, set query type string to update if there is.
    $query = "SELECT * FROM daily WHERE day = '$date'";
    $result = pg_query($db, $query);
    if (!$result) {
        echo "Error executing the query.";
        exit;
    }
    while ($row = pg_fetch_assoc($result)) {
        if ($row['day'] == $date) {
            $insert = false;
        }
    }
    pg_free_result($result);

    // Insert record
    if ($insert) {
        $query = "INSERT INTO daily (cash, eftpos, day) VALUES ($cash, $eftpos, '$date')";
        $result = pg_query($db, $query);
        if (!$result) {
            echo "Error executing the insert query.";
            exit;
        }
        echo "Inserted record for today.";
        pg_close($db);
    }
    else if ($insert == false) {
        $query = "UPDATE daily SET cash = $cash WHERE day = '$date'";
        $result = pg_query($db, $query);
        if (!$result) {
            echo "Error executing the insert query.";
            exit;
        }
        echo "Updated record for today.";
        pg_close($db);
    }
}
function insertEftpos($eftpos) {

    if (!$authorise = checkAuthentication()) {
        return false;
    }

    $date = date('Y-m-d');
    $db = dbConnect();
    $insert = true;
    $cash = 0;

    // Check for an existing record for today, set query type string to update if there is.
    $query = "SELECT * FROM daily WHERE day = '$date'";
    $result = pg_query($db, $query);
    if (!$result) {
        echo "Error executing the query.";
        exit;
    }
    while ($row = pg_fetch_assoc($result)) {
        if ($row['day'] == $date) {
            $insert = false;
        }
    }
    pg_free_result($result);

    // Insert record
    if ($insert) {
        $query = "INSERT INTO daily (cash, eftpos, day) VALUES ($cash, $eftpos, '$date')";
        $result = pg_query($db, $query);
        if (!$result) {
            echo "Error executing the insert query.";
            exit;
        }
        echo "Inserted record for today.";
        pg_close($db);
    }
    else if ($insert == false) {
        $query = "UPDATE daily SET eftpos = $eftpos WHERE day = '$date'";
        $result = pg_query($db, $query);
        if (!$result) {
            echo "Error executing the insert query.";
            exit;
        }
        echo "Updated record for today.";
        pg_close($db);
    }
}
?>