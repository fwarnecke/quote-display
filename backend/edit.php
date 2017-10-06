<?php
session_start();
if ( !isset( $_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: index.php');
}
include '../path.php';
include '../class/templateInterface.class.php';
include '../class/template.class.php';
include '../class/db_sql.class.php';

$site = new Template('backend');

$content = new Template('edit');
  
if ($_POST) {
    if ( !empty( $_POST['quote'])) {
        if ( empty( $_POST['author'])) {
            $_POST['author'] = 'John Doe';
        }

        if ( array_key_exists( 'color', $_POST) && !empty( $_POST['color'])) {
            $_POST['color'] = intval($_POST['color']);
        } else {
            $_POST['color'] = 0;
        }

        var_dump($_POST);

        $db = DB_SQL::getInstance();
        if ( isset($_GET) && array_key_exists( 'id', $_GET) && !empty( $_GET['id'])) {
            $query = 'UPDATE tbl_quote SET quote_author = :author, quote_text = :quote, quote_status = "DRAFTED", color_id= :color WHERE quote_id = :id';
            $stmt = $db->prepare( $query);
            $stmt->bindValue( ':id', $_GET['id']);
        } else {
            $query = 'INSERT INTO tbl_quote (quote_author, quote_text, color_id) VALUES ( :author, :quote, :color)';
            $stmt = $db->prepare( $query);
        }
        $patterns = array( '/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/');
        $replacements = array( '&Auml;', '&Ouml;', '&Uuml;', 'a&uml;', 'o&uml;', '&uuml;');
        $_POST['author'] = htmlspecialchars( $_POST['author']);
        $_POST['quote'] = htmlspecialchars( $_POST['quote']);
        $_POST['author'] = preg_replace( $patterns, $replacements, $_POST['author']);
        $_POST['quote'] = preg_replace( $patterns, $replacements, $_POST['quote']);
        $stmt->bindValue( ':author', $_POST['author']);
        $stmt->bindValue( ':quote', $_POST['quote']);
        $stmt->bindValue( ':color', $_POST['color']);
        if ( $stmt->execute()) {
            header('Location: backend.php');
        } else {
            $error = 'Error in datatransmission. Please try again.';
        }
    } else {
        $error = 'Please insert a quote.';
    }
    //Errorhandling
    if ( isset( $error) && !empty( $error)) {
        $content->addLoopIteration( 'errorLoop', array( 'msg' => $error));
        $content->replace( 'quote_author', $_POST['author']);
        $content->replace( 'quote_text', $_POST['quote']);
        $site->include( 'content', $content);
        $site->replace( 'quote_id', $_GET['id']);
        $site->replace( 'NewEdit', 'New');
    }
} elseif ($_GET && array_key_exists( 'id', $_GET) && !empty($_GET['id'])) {
    $query =   'SELECT
                    tbl_quote.quote_text,
                    tbl_quote.quote_author,
                    tbl_quote.color_id AS quote_color_id,
                    tbl_color.color_code AS quote_color_code,
                    tbl_color.color_name AS quote_color_name
                FROM tbl_quote
                LEFT JOIN tbl_color
                    ON  tbl_quote.color_id = tbl_color.color_id
                WHERE quote_id = ?';
    $db = DB_SQL::getInstance();
    $stmt = $db->prepare($query);
    if ( $stmt->execute( array( $_GET['id']))) {
        if ( $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['old'] = $row;

            $content->replace( 'quote_author', $row['quote_author']);
            $content->replace( 'quote_text', $row['quote_text']);
            $content->replace( 'quote_color_id', $row['quote_color_id']);
            $content->replace( 'quote_color_code', $row['quote_color_code']);
            $content->replace( 'quote_color_name', $row['quote_color_name']);

            $content->replace( 'NewEdit', 'Edit');

            $site->replace( 'NewEdit', 'Edit');
            $site->replace( 'quote_id', $_GET['id']);
        } else {
            exit('Quote does not exist. <a href="backend.php">Back</a>');
        }
    } else {
        exit('Error in datatransmission. Please try again. <a href="backend.php">Back</a>');
    }
} else {
    $content->replace( 'quote_author', '');
    $content->replace( 'quote_text', '');
    $content->replace( 'quote_color_id', '');
    $content->replace( 'quote_color_code', '');
    $content->replace( 'quote_color_name', '');

    $content->replace( 'NewEdit', 'New');

    $site->replace( 'NewEdit', 'New');
    $site->replace( 'quote_id', '');
}

$site->replace( 'activeQuotes', '');
$site->replace( 'activeEdit', 'active');
$site->replace( 'activeSettings', '');

$query = 'SELECT * FROM tbl_color WHERE color_id != 0';
$db = DB_SQL::getInstance();
$stmt = $db->prepare($query);
if ( $stmt->execute()) {
    $active_color = (isset($row) && array_key_exists( 'quote_color_id', $row))? $row['quote_color_id']: 0;
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['checked'] = ($row['color_id'] == $active_color) ? 'checked' : '' ;
        $content->addLoopIteration('colorLoop', $row);
    }
}

$site->include( 'content', $content);

$site->showSite();