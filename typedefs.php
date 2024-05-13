<?php

/**
 * This script will read the cache dir and print out all the typedefs in a "raw" format. This needs to be processed (see README.md)
 */

include "./vendor/autoload.php";

// read all files in ./cache dir. This assumes that you have ran definitions.php before to create the cache dir.
$files = scandir("./cache");
foreach ($files as $file) {
    if (is_dir("./cache/".$file)) {
        continue;
    }

    $html = file_get_contents("./cache/".$file);
    $doc = new DOMDocument();
    @$doc->loadHTML($html);

    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("//*[starts-with(@id,'typedef-')]/ancestor::pre");

    foreach ($nodes as $node) {
        print $node->textContent . "\n";
    }
}
