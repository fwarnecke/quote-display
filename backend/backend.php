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

if ($_GET) {
    //Status change
    if ( array_key_exists( 'changeStatus', $_GET) && array_key_exists( 'oldStatus', $_GET)) {
        if ( !empty($_GET['changeStatus']) && !empty($_GET['oldStatus'])) {
            $query = 'UPDATE tbl_quote SET quote_status = :status WHERE quote_id = :id';
            $db = DB_SQL::getInstance();
            $stmt = $db->prepare($query);
            $stmt->bindValue( ':id', $_GET['changeStatus']);

            if ( $_GET['oldStatus'] == 'DRAFTED' || $_GET['oldStatus'] == 'DISABLED') {
                $stmt->bindValue( ':status', 'PUBLISHED');
            } else {
                $stmt->bindValue( ':status', 'DISABLED');
            }

            if ( $stmt->execute()) {
                header('Location: backend.php');
            } else {
                exit('Error in datatransmission. Please try again. <a href="backend.php">Back</a>');
            }
        }
    //Delete
    } elseif ( array_key_exists( 'delete', $_GET)) {
        if ( !empty($_GET['delete'])) {
            $query = 'DELETE FROM tbl_quote WHERE quote_id = ?';
            $db = DB_SQL::getInstance();
            $stmt = $db->prepare($query);

            if ( $stmt->execute( array( $_GET['delete']))) {
                header('Location: backend.php');
            } else {
                exit('Error in datatransmission. Please try again. <a href="backend.php">Back</a>');
            }
        }
    //Edit/New
    }
} else {
    //Load normal Quotepage

    $site->replace( 'activeQuotes', 'active');
    $site->replace( 'activeEdit', '');
    $site->replace( 'activeSettings', '');
    $site->replace( 'NewEdit', 'New');
    $site->replace( 'quote_id', '');

    $content = new Template('quotes');

    $db = DB_SQL::getInstance();
    $query =   'SELECT  tbl_quote.quote_id,
                        tbl_quote.quote_text,
                        tbl_quote.quote_author,
                        tbl_color.color_code AS quote_color_code,
                        tbl_color.color_name AS quote_color_name,
                        tbl_quote.quote_status
                FROM tbl_quote
                LEFT JOIN tbl_color
                    ON  tbl_quote.color_id = tbl_color.color_id;';
    $stmt = $db->prepare($query);
    if ( $stmt->execute()) {
        while ( $row = $stmt->fetch( PDO::FETCH_ASSOC)) {
            switch ($row['quote_status']) {
                case 'DRAFTED':
                    $row['statusCOption'] = 'Publish';
                    break;
                
                case 'PUBLISHED':
                    $row['statusCOption'] = 'Disable';
                    break;

                case 'DISABLED':
                    $row['statusCOption'] = 'Enable';
                    break;
                
                default:
                    $row['statusCOption'] = 'Enable';
                    break;
            }
            $content->addLoopIteration( 'contentLoop', $row);
        }
    }

    $site->include( 'content', $content);
}

$site->showSite();