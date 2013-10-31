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
        'host' => 'smtp.gmail.com',
        'connection_class' => 'login',
        'connection_config' => array(
            'ssl' => 'tls',
            'username' => 'dan.ferguson.mo@gmail.com',
            'password' => 'nhebiemail'
        ),
        'port' => 587
    ),
    'options_workforce' => array(
        'host' => 'localhost',
        'connection_class' => 'login',
        'connection_config' => array(
            //'ssl' => 'tls',
            'username' => 'no-reply@workforceproject.org',
            'password' => 'p0w3r#U$3r_'
        ),
        'port' => 587
    ),
    'options_max' => array(
        'host' => 'localhost',
        'connection_class' => 'login',
        'connection_config' => array(
            //'ssl' => 'tls',
            'username' => 'no-reply@maximizingresources.org',
            'password' => 'p0w3r#U$3r_'
        ),
        'port' => 587
    ),


    /**
     * End of GoalioMailService configuration
     */
);

// Study-specific email config
if ($_SERVER['HTTP_HOST'] == 'workforceproject.org') {
    $settings['options'] = $settings['options_workforce'];
} elseif ($_SERVER['HTTP_HOST'] == 'maximizingresources.org') {
    $settings['options'] = $settings['options_max'];
}

/**
 * You do not need to edit below this line
 */
return array(
    'goaliomailservice' => $settings,
);
