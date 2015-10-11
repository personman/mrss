<?php

// Dump a variable in pre tags with print_r. Show the caller file so we can find them
function pr($var, $callee = null) {
    $id = uniqid();
    if (empty($callee))	list($callee) = debug_backtrace();

    echo '<div style="font-family:Courier; font-size: 10px; margin: 20px 20px 0px 20px; background: #333; color: #fff; padding: 10px"><a href="#" onclick="document.getElementById(\'pre_' . $id . '\').style.display = \'none\'; return false;" style="color:white; text-decoration: none">[-]</a> ' . $callee['file'].' @ line: '.$callee['line'] . '</div>';
    echo '<pre id="pre_' . $id . '" style="margin:0px 20px 20px 20px;padding:20px;border:1px solid #aaa; text-align: left">';
    print_r($var);
    echo '</pre>';
}

function prd($var) {
    list($callee) = debug_backtrace();
    pr($var, $callee);
    die;
}

function takeYourTime()
{
    ini_set('memory_limit', '512M');
    set_time_limit(5600);
}
