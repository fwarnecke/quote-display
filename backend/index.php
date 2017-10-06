<?php
include '../path.php';
include '../class/db_sql.class.php';
include '../class/templateInterface.class.php';
include '../class/template.class.php';


if ( $_GET) {
    session_start();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php');
}
if ( $_POST) {
    if ( ( isset( $_POST['user_name']) && $_POST['user_name']) && ( isset( $_POST['user_pw']) && $_POST['user_pw'])) {
        $db = DB_SQL::getInstance();
        $query =   'SELECT user_pwSalt, user_pw, user_id
                    FROM tbl_user
                    WHERE user_name = ? AND user_activated = TRUE';
        $stmt = $db->prepare($query);
        if ( $stmt->execute( array( $_POST['user_name']))) {
            if ( $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pwhash = hash_pbkdf2( 'sha256', $_POST['user_pw'], $row['user_pwSalt'], 20000, 32);
                if ( $row['user_pw'] == $pwhash) {
                    session_start();
                    $_SESSION['user_id'] = $row['user_id'];
                    header('Location: backend.php');
                }
            }
            $site = new Template('login');
            $site->addLoopIteration( 'errorLoop', array( 'msg' => 'Username or password wrong. Please try again.'));
            $site->showSite();
        } else {
            exit('DBERROR1');
        }
    } else {
        $site = new Template('login');
        $site->showSite();
    }
} else {
    $site = new Template('login');
    $site->showSite();
}