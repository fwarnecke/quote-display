<?php

include 'path.php';

include './class/templateInterface.class.php';
include './class/template.class.php';

$file = 'settings.cfg';

if ( file_exists( $file)) {
    $settings = json_decode( file_get_contents( $file), true);
} else {
    //usedefault
}

$site = new Template('index');

$countX = intval(substr( $settings['count'], 0, 1));
$countY = intval(substr( $settings['count'], 2));

$site->replace('row_count', $countY);
$site->replace('col_count', $countX);

$quote_nr = 0;
for ($i=0; $i < $countY; $i++) {
    $row = new Template('quoteRow');
    $row->replace('row_nr', $i);
    for ($j=0; $j < $countX; $j++) { 
        $row->addLoopIteration('loop', array( 'column_nr' => $j, 'nr' => $quote_nr));
        $quote_nr ++;
    }
    $site->addLoopIteration('rowLoop', array());
    $site->include('quoteRow', $row);
}




$site->replace( 'settingsJSON', json_encode( $settings));

$site->showSite();
