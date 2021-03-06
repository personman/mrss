<?php
/**
 * GoalioForgotPassword Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(

    /**
     * Email Address that will appear in the 'From' of outbound emails
     *
     * Default: empty
     */
    'email_from_address' => array(
    	'email' => 'info@benchmarkinginstitute.org',
    	'name' => 'NHEBI Staff',
	),

    /**
     * Subject line of the email message which is
     * sent out when a user enters their email address
     *
     * Default: 'You requested to reset your password'
     */
    //'reset_email_subject_line' => '(test) You requested to reset your password',

    //'reset_email_template' => 'fake',

    /**
     * Mail Transport to use
     *
     * Default: 'Zend\Mail\Transport\Sendmail'
     */
    //'email_transport' => 'Zend\Mail\Transport\Sendmail',

    /**
     * Password Model Entity Class
     *
     * Name of Entity class to use. Useful for using your own entity class
     * instead of the default one provided. Default is GoalioForgotPassword\Entity\Password.
     */
    //'password_entity_class' => 'GoalioForgotPassword\Entity\Password',

    /**
     * Reset expire time
     *
     * How long will the user be able to reset the password using the token?
     *
     * Default value: 86400 seconds = 24 hours
     * Accepted values: the number of seconds the user should be allowed to change his password
     */
    //'reset_expire' => 86400,

    /**
     * End of GoalioForgotPassword configuration
     */
);

// Study-specific email config
/*if (!empty($_SERVER['HTTP_HOST'])) {
    if ($_SERVER['HTTP_HOST'] == 'workforceproject.org') {
        $settings['email_from_address']['email'] = 'no-reply@workforceproject.org';
    } elseif (strpos($_SERVER['HTTP_HOST'], 'maximizingresources.org') !== false) {
        $settings['email_from_address']['email'] = 'no-reply@maximizingresources.org';
    }
}
*/

/**
 * You do not need to edit below this line
 */
return array(
    'goalioforgotpassword' => $settings,
);
