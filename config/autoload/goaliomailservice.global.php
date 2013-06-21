<?php
/**
 * GoalioMailService Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(

    /**
     * Transport Class
     *
     * Name of Zend Transport Class to use
     */
    'transport_class' => 'Zend\Mail\Transport\Smtp',
    
    'options_class' => 'Zend\Mail\Transport\SmtpOptions',
    
    'options' => array(
        /*'host' => 'smtp.gmail.com',
        'connection_class' => 'login',
        'connection_config' => array(
            'ssl' => 'tls',
            'username' => 'dan.ferguson.mo@gmail.com',
            'password' => 'nhebiemail' (port 587*/
        'host' => 'max.maximizingresources.org',
        'connection_class' => 'login',
        'connection_config' => array(
            'ssl' => 'tls',
            'username' => 'no-reply@workforceproject.org',
            'password' => 'p0w3r#U$3r_'
        ),
        'port' => 465
    ),

    /**
     * End of GoalioMailService configuration
     */
);

/**
 * You do not need to edit below this line
 */
return array(
    'goaliomailservice' => $settings,
);
