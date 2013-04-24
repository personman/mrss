<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Home',
                'uri' => '/'
            ),
            array(
                'label' => 'Imports',
                'uri' => '/import'
            ),
            array(
                'label' => 'Colleges',
                'uri' => '/colleges'
            ),
            array(
                'label' => 'Benchmarks',
                'uri' => '/benchmarks'
            ),
            array(
                'label' => 'Pages',
                'uri' => '/pages'
            ),
            array(
                'label' => 'User',
                'uri' => '/user'
            )
        )
    )
);
