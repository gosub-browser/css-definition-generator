<?php

/**
 * This script will compare the properties in the index with the definitions
 * and print out the properties that are missing.
 */

$source = file_get_contents("https://www.w3.org/Style/CSS/all-properties.en.json");
$propIndex = json_decode($source, true);

$definitions = json_decode(file_get_contents("definitions.json"), true);

foreach ($propIndex as $idx => $entry) {
    if (str_starts_with($entry['property'], "--")) {
        continue;
    }

    $name = $entry['property'];

    if (isset($definitions[$name])) {
        continue;
    }

    print "Missing: $name\n";
}
