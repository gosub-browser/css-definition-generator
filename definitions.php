<?php

/**
 * This script will fetch all the CSS properties from the W3C website and extract the definitions.
 */

$source = file_get_contents("https://www.w3.org/Style/CSS/all-properties.en.json");
$propIndex = json_decode($source, true);

$definitions = [];
foreach ($propIndex as $idx => $entry) {
    if (str_starts_with($entry['property'], "--")) {
        continue;
    }

    printf("Fetching property: %-30s: %s\n", $entry['property'], $entry['url']);

    // fetch the part behind the #
    $url = parse_url($entry['url']);
    $fragment = $url['fragment'];
    if (str_starts_with($fragment, "propdef-")) {
        $fragment = substr($fragment, 8);
    }

    // Cache the HTML
    $fn = "cache/".sha1($entry['url']);
    if (file_exists($fn)) {
        $html = file_get_contents($fn);
    } else {
        $html = file_get_contents($entry['url']);
        file_put_contents($fn, $html);
    }


    // Read the document and try and find the definition
    $doc = new DOMDocument();
    @$doc->loadHTML($html);

    // The handler functions will be able to parse different layouts
    $handlers = [
        "handler1",
        "handler2",
        "handler3",
    ];
    foreach ($handlers as $handler) {
        $def = $handler($doc, $fragment);
        if ($def) {
            break;
        }
    }

    if (!$def) {
        printf("  No definition found for $fragment\n");
        continue;
    }

    $definitions[$entry['property']] = $def;
    file_put_contents("definitions.json", json_encode($definitions, JSON_PRETTY_PRINT));
}


function handler1($doc, $fragment): ?array {
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("//*[@data-link-for-hint='$fragment']");

    if ($nodes->length == 0) {
        return null;
    }

    $parts = explode("\n", $nodes->item(0)->textContent);
    for ($i = 0; $i < count($parts); $i++) {
        if (trim($parts[$i]) == "Value:") {
            $value = trim($parts[$i + 1]);
        }
        if (trim($parts[$i]) == "Initial:") {
            $initial = trim($parts[$i + 1]);
        }
        if (trim($parts[$i]) == "Inherited:") {
            $inherited = trim($parts[$i + 1]) == "yes";
        }
    }

    return [
        'value' => $value,
        'initial' => $initial,
        'inherited' => $inherited,
    ];
}

function handler2($doc, $fragment): ?array {
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("//span[@class='index-def' and @title=\"'$fragment'\"]/ancestor::dt/following-sibling::dd//table[@class='propinfo']");
    if ($nodes->length == 0) {
        return null;
    }

    $node = $nodes->item(0);
    return [
        'value' => trim($node->getElementsByTagName('td')->item(1)->textContent),
        'initial' => trim($node->getElementsByTagName('td')->item(3)->textContent),
        'inherited' => trim($node->getElementsByTagName('td')->item(5)->textContent) == "yes",
    ];
}

function handler3($doc, $fragment): ?array {
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("//dfn[@id='propdef-$fragment']/ancestor::table");
    if ($nodes->length == 0) {
        return null;
    }

    $node = $nodes->item(0);
    return [
        'value' => trim($node->getElementsByTagName('td')->item(1)->textContent),
        'initial' => trim($node->getElementsByTagName('td')->item(2)->textContent),
        'inherited' => trim($node->getElementsByTagName('td')->item(4)->textContent) == "yes",
    ];
}
