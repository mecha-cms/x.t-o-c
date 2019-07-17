<?php

// `0`: Disable TOC
// `1`: Automatic section ID and TOC
// `2`: Automatic section ID only

// `true`: Alias for `1`
// `false`: Alias for `0`

return [
    'type' => 1,
    'id' => [
        0 => 't-o-c:%s',
        1 => 'stage:%s',
        2 => 'a:%s'
    ],
    'class' => [
        0 => 't-o-c',
        1 => 'is:t-o-c',
        2 => 'a:t-o-c',
        3 => 'not:t-o-c'
    ]
];