<?php
/**
 * ZfcUserImpersonate Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can drop this config file in it and change
 * the values as you wish.
 */
$settings = array(
    /**
     * User Id Route Parameter
     *
     * The name of the route parameter that specifies the user id, passed as part of the impersonate route
     *
     * Accepted values: string
     */
    //'user_id_route_parameter'      => 'userId',

    /**
     * Impersonate Redirect Route
     *
     * The name of the route to which the user will be redirected after beginning impersonation.
     *
     * Accepted values: string
     */
    //'impersonate_redirect_route'   => 'zfcuser',

    /**
     * Unimpersonate Redirect Route
     *
     * The name of the route to which the user will be redirected after ending impersonation.
     *
     * Accepted values: string
     */
    //'unimpersonate_redirect_route' => 'zfcuser',

    /**
     * Store user to session as object (true) or id (false)
     *
     * Set to false if you want to have the user object rebuilt from the database for each request.
     *
     * Accepted values: boolean
     */
    'store_user_as_object' => false,

);

/**
 * You do not need to edit below this line
 */
return array(
    'zfcuserimpersonate' => $settings,
);
