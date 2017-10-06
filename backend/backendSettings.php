<?php
session_start();
if ( !isset( $_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: index.php');
}

include '../path.php';
include '../class/templateInterface.class.php';
include '../class/template.class.php';
include '../class/db_sql.class.php';

$file = '../settings.cfg';
$tmplConfigDir = '../templates/templateConfig/';

if ($_POST) {
    if ($_POST['animation'] == 'move') {
        $_POST['delayed'] = 'false';
    }
    file_put_contents( $file, json_encode( $_POST));
}


$site = new Template('backend');

$site->replace( 'activeQuotes', '');
$site->replace( 'activeEdit', '');
$site->replace( 'activeSettings', 'active');
$site->replace( 'NewEdit', 'New');
$site->replace( 'quote_id', '');

$content = new Template('settings');

if ( file_exists( $file)) {
    $settings = json_decode( file_get_contents( $file), true);
} else {
    exit('Settings missing.');
}


//ANIMATION
if ( file_exists( $tmplConfigDir .'animation.cfg')) {
    $aniConfig = json_decode( file_get_contents( $tmplConfigDir .'animation.cfg'), true);
} else {
    exit('Settings missing.(animation)');
}
foreach ($aniConfig as $value) {
    $content->addLoopIteration( 'animationOptionLoop', $value);
    if ( in_array( $value['value'], $settings)) {
        $content->replace( 'selected', 'selected');
    } else {
        $content->replace( 'selected', '');
    }
}
$disableDelay = ($settings['animation'] == "move")? true:false;
unset($settings['animation']);

//COUNT
if ( file_exists( $tmplConfigDir .'count.cfg')) {
    $countConfig = json_decode( file_get_contents( $tmplConfigDir .'count.cfg'), true);
} else {
    exit('Settings missing.(count)');
}
foreach ($countConfig as $value) {
    $content->addLoopIteration( 'countOptionLoop', ['name' => $value['size'], 'value' => $value['size']]);
    if ( in_array( $value['size'], $settings)) {
        $content->replace( 'selected', 'selected');
    } else {
        $content->replace( 'selected', '');
    }
}
unset($settings['count']);

//COLORS
$db = DB_SQL::getInstance();
$query =   'SELECT
                color_id,
                color_name,
                color_code
            FROM tbl_color
            WHERE color_id != 0';
$stmt = $db->prepare($query);

if ($stmt->execute()) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $content->addLoopIteration('colorQOptionLoop', $row);
        if ( $settings['colorQ'] == $row['color_code']) {
            $content->replace( 'checked', 'checked');
        } else {
            $content->replace( 'checked', '');
        }
        $content->addLoopIteration('colorAOptionLoop', $row);
        if ( $settings['colorA'] == $row['color_code']) {
            $content->replace( 'checked', 'checked');
        } else {
            $content->replace( 'checked', '');
        }
    }
}
unset($settings['colorQ']);
unset($settings['colorA']);

//DELAYED
if( $disableDelay) {
    $content->replace( 'delayY_checked', 'disabled');
    $content->replace( 'delayN_checked', 'checked disabled');
} else {
    if ($settings['delayed'] == 'true') {
        $content->replace( 'delayY_checked', 'checked');
        $content->replace( 'delayN_checked', '');
    } else {
        $content->replace( 'delayY_checked', '');
        $content->replace( 'delayN_checked', 'checked');
    }
}
unset($settings['delayed']);

//MISSING SETTINGS
foreach ($settings as $key => $value) {
    $content->replace( $key, $value);
}



$site->include( 'content', $content);

$site->showSite();

