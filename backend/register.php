<?php

include '../path.php';
include '../class/db_sql.class.php';
if ( $_POST) {
    $db = DB_SQL::getInstance();
    $query = 'INSERT INTO tbl_user (user_name, user_pw, user_pwSalt) VALUES (?, ?, ?)';
    $stmt = $db->prepare($query);
    $stmt->bindValue( 1, $_POST['user_name']);
    $salt = bin2hex(random_bytes(16));
    $pwhash = hash_pbkdf2( 'sha256', $_POST['user_pw'], $salt, 20000, 32);
    $stmt->bindValue( 2, $pwhash);
    $stmt->bindValue( 3, $salt);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'failure';
    }
} else {
    echo '  <form method="post">
                <input name="user_name" type="text">
                <input name="user_pw" type="password">
                <input type="submit">
            </form>';
}