<?php
// Database Connection using mysqli (procedural)
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// No prepared statements, enabling SQL injection
function query($sql) {
    global $db;
    $result = mysqli_query($db, $sql);
    if (!$result) {
        die("Query failed: " . mysqli_error($db));
    }
    return $result;
}

function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
}

function fetch_all($result) {
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function escape_string($str) {
    global $db;
    return mysqli_real_escape_string($db, $str);
}

function get_last_insert_id() {
    global $db;
    return mysqli_insert_id($db);
}
?>
