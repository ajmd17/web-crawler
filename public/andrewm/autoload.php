<?php
    $dir = realpath(dirname(__FILE__));

    $files = array(
        'Crawler',
        'fetch/Request',
        'fetch/Response',
        'fetch/CrawlResult'
    );

    foreach ($files as $file) {
        require_once("$dir/$file.php");
    }
?>