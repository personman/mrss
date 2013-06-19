<?php
/**
 * This is a sample "local" configuration for your application. To use it, copy 
 * it to your config/autoload/ directory of your application, and edit to suit
 * your application.
 *
 * This configuration example demonstrates using an SMTP mail transport, a
 * ReCaptcha CAPTCHA adapter, and setting the to and sender addresses for the
 * mail message.
 */
return array(
    'phly_contact' => array(
        // This is simply configuration to pass to Zend\Captcha\Factory
        'captcha' => array(
            'class'   => 'recaptcha',
            'options' => array(
                'pubkey'  => '6Lf_pOISAAAAAE6_77NYlkBeU0sbZsudXiXIlF-0',
                'privkey' => '6Lf_pOISAAAAAJ0fHOk8IzvseM5-rUTBDnCnxatY',
            ),
        ),


        // This sets the default "to" and "sender" headers for your message
        'message' => array(
            /*
            // These can be either a string, or an array of email => name pairs
            'to'     => 'contact@your.tld',
            'from'   => 'contact@your.tld',
            // This should be an array with minimally an "address" element, and 
            // can also contain a "name" element
            'sender' => array(
                'address' => 'contact@your.tld'
            ),
             */
            'to' => array(
                'dfergu15@jccc.edu',
                'mtaylo24@jccc.edu'
            ),

            'from' => 'dfergu15@jccc.edu'
        ),

        // Transport consists of two keys: 
        // - "class", the mail tranport class to use, and
        // - "options", any options to use to configure the 
        //   tranpsort. Usually these will be passed to the 
        //   transport-specific options class
        // This example configures GMail as your SMTP server
        'mail_transport' => array(
            'class'   => 'Zend\Mail\Transport\Smtp',
            'options' => array(
                'host'             => 'smtp.gmail.com',
                'port'             => 587,
                'connectionClass'  => 'login',
                'connectionConfig' => array(
                    'ssl'      => 'tls',
                    'username' => 'dan.ferguson.mo@gmail.com',
                    'password' => 'nhebiemail'
                ),
            ),
        ),
    ),
);
