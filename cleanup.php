<?php

$data = file($argv[1]);

$typedefs = [];

$lp = 0;
$cur = $data[$lp];
while ($lp < count($data)) {
    $lp++;

    // Some elements are multiline and start with a space, |, ) or ]
    if ($data[$lp][0] == ' ' || $data[$lp][0] == '|' || $data[$lp][0] == ')' || $data[$lp][0] == ']') {
        $cur .= $data[$lp];
    } else {
        $cur = str_replace("\n", "", $cur);
        $cur = preg_replace('/\s+/', ' ', $cur);

        if (!empty($cur)) {
            // deduplicate by overwriting same values instead of appending it in a list
            $typedefs[md5($cur)] = $cur;
        }
        $cur = $data[$lp];
    }
}

$typedefs = array_values($typedefs);
sort($typedefs);

print json_encode($typedefs, JSON_PRETTY_PRINT);
