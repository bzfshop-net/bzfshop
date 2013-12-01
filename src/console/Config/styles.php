<?php

$styleArray = array(

    //Foreground color options
    'black'         => "\033[0;30m",
    'black_bold'    => "\033[1;30m",
    'red'           => "\033[0;31m",
    'red_bold'      => "\033[1;31m",
    'green'         => "\033[0;32m",
    'green_bold'    => "\033[1;32m",
    'brown'         => "\033[0;33m",
    'yellow'        => "\033[1;33m",
    'blue'          => "\033[0;34m",
    'blue_bold'     => "\033[1;34m",
    'purple'        => "\033[0;35m",
    'purple_bold'   => "\033[1;35m",
    'cyan'          => "\033[0;36m",
    'cyan_bold'     => "\033[1;36m",
    'white'         => "\033[0;37m",
    'white_bold'    => "\033[1;37m",
    //Background color options
    'black_bg'      => "\033[40m",
    'red_bg'        => "\033[41m",
    'green_bg'      => "\033[42m",
    'yellow_bg'     => "\033[43m",
    'blue_bg'       => "\033[44m",
    'magenta_bg'    => "\033[45m",
    'cyan_bg'       => "\033[46m",
    'light_gray_bg' => "\033[47m",
    //Text reset
    'reset'         => "\033[0m",

);

// windows do not support color, so we do not colorize the output
if (false !== strstr(strtolower(php_uname('s')), 'windows')) {
    $newStyleArray = array();
    foreach ($styleArray as $key => $ignore) {
        $newStyleArray[$key] = '';
    }
    $styleArray = $newStyleArray;
}

return $styleArray;
