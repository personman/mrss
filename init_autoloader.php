<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication
 * for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc.
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
}

$zfPath = false;

if (is_dir('vendor/ZF2/library')) {
    $zfPath = 'vendor/ZF2/library';
} elseif (getenv('ZF2_PATH')) {
    // Support for ZF2_PATH environment variable or git submodule
    $zfPath = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zfPath = get_cfg_var('zf2_path');
}

if ($zfPath) {
    if (isset($loader)) {
        $loader->add('Zend', $zfPath);
    } else {
        include $zfPath . '/Zend/Loader/AutoloaderFactory.php';
        Zend\Loader\AutoloaderFactory::factory(
            array(
                'Zend\Loader\StandardAutoloader' => array(
                    'autoregister_zf' => true
                )
            )
        );
        require $zfPath . '/Zend/Stdlib/compatibility/autoload.php';
        require $zfPath . '/Zend/Session/compatibility/autoload.php';
    }
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException(
        'Unable to load ZF2. Run `php composer.phar install`
        or define a ZF2_PATH environment variable.'
    );
}
