<?php

/* Amazon SES (low deliverability)
$smtp = array(
    'host' => 'email-smtp.us-west-2.amazonaws.com',
    'port'             => 587,
    'connectionClass'  => 'login',
    'connectionConfig' => array(
        'ssl'      => 'tls',
        'username' => 'AKIAJWKBUWWGMQJ2QZUQ',
        'password' => 'AqtPCY2L7TQAohN+ak5zS1BtleDmynUQ4ApOmdNqpCB9'
    ),
);*/

$smtp = array(
    'host' => 'smtp.sendgrid.net',
    'port'             => 587,
    'connectionClass'  => 'login',
    'connectionConfig' => array(
        'ssl'      => 'tls', // apikey
        'username' => 'apikey',
        'password' => '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',
    ),
);

$settings = array(
    'phly_contact' => array(
        'mail_transport' => array(
            'options' => $smtp
        )
    ),
    'goaliomailservice' => array(
        'transport_options' => $smtp
    )
);


return $settings;
