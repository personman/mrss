<?php

$smtp = array(
    'host' => 'email-smtp.us-west-2.amazonaws.com',
    'port'             => 587,
    'connectionClass'  => 'login',
    'connectionConfig' => array(
        'ssl'      => 'tls',
        'username' => 'AKIAJWKBUWWGMQJ2QZUQ',
        'password' => 'AqtPCY2L7TQAohN+ak5zS1BtleDmynUQ4ApOmdNqpCB9'
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
