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
    ini_set('memory_limit', '1024M');
    set_time_limit(5600);
}


function getStates($includeBlankOption = true, $includeCanada = false)
{
    $states = array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District Of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        'FM' => 'Federated States of Micronesia',
        'GU' => 'Guam',
        'PR' => 'Puerto Rico',
        'MH' => 'Marshall Islands',
        'VI' => 'Virgin Islands of the U.S.',
    );

    if ($includeCanada) {
        $provinces = array(
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon'
        );

        $states = array_merge($states, $provinces);
    }

    if ($includeBlankOption) {
        $blankOption = array(
            '' => 'Select State'
        );

        $states = array_merge($blankOption, $states);
    }

    return $states;
}

function autoDetectLineEndings()
{
    ini_set('auto_detect_line_endings', true);
}

function getSeriesColors()
{
    return array(
        'seriesColors' => array(
            '#9cc03e', // '#005595' lightened 40%
            '#3366B4', // '#519548' lightened 30%
            '#9b62c9',
            '#ebb164',
            '#F55',
            '#5F5',
            '#55F',
            '#5FF'
        ),
        'yourCollegeColors' => array(
            '#507400',
            '#001A68',
            '#65318F',
            '#db891b',
            '#F00',
            '#0F0',
            '#00F',
            '#0FF',
        )
    );
}

function isDev()
{
    return ($_SERVER['REMOTE_ADDR'] == '216.185.233.187');
}
