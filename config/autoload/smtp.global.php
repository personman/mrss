<?php

$smtp = array(
    'host' => 'smtp.sendgrid.net',
    'port'             => 587,
    'connectionClass'  => 'login',
    'connectionConfig' => array(
        'ssl'      => 'tls', // apikey
        'username' => 'apikey',
        'password' => 'password-goes-here',
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
