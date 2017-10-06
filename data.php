<?php
include 'path.php';

include './class/db_sql.class.php';

$data = array();

$db = DB_SQL::getInstance();
$query =   'SELECT
                tbl_quote.quote_text,
                tbl_quote.quote_author,
                tbl_color.color_code
            FROM tbl_quote
            LEFT JOIN tbl_color
                ON  tbl_quote.color_id = tbl_color.color_id
            WHERE quote_status = "PUBLISHED"';
$stmt = $db->prepare($query);
if ( $stmt->execute()) {
    while ( $row = $stmt->fetch(PDO::FETCH_NUM)) {
        $data[] = $row;
    }
}

if ( $_POST && array_key_exists( 'req', $_POST) && isset( $_POST['req']) && $_POST['req'] === "initial") {

    //Benachrichtigung
    $from_name = "QuoteSystem";
    $from_mail = "quoteSys@gruenspar.com";
    $mail_subject = "QuoteSystem start";
    $mail_to = "leuderalbert.j@gmail.com";
    $mail_message = "QuoteSystem booted successfully and data was transfered";

    $encoding = "utf-8";

    // Preferences for Subject field
    $subject_preferences = array(
        "input-charset" => $encoding,
        "output-charset" => $encoding,
        "line-length" => 76,
        "line-break-chars" => "\r\n"
    );

    // Mail header
    $header = "Content-type: text/html; charset=".$encoding." \r\n";
    $header .= "From: ".$from_name." <".$from_mail."> \r\n";
    $header .= "MIME-Version: 1.0 \r\n";
    $header .= "Content-Transfer-Encoding: 8bit \r\n";
    $header .= "Date: ".date("r (T)")." \r\n";
    $header .= iconv_mime_encode("Subject", $mail_subject, $subject_preferences);

    // Send mail
    mail($mail_to, $mail_subject, $mail_message, $header);
}


echo json_encode($data);