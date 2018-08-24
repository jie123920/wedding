<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('The Facebook SDK requires PHP version 5.4 or higher.');
}

/**
 * Register the autoloader for the Facebook ShopPay classes.
 *
 * Based off the official PSR-4 autoloader example found here:
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'app\\Library\\ShopPay\\';
    // base directory for the namespace prefix
    $baseDir = __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relativeClass = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relativeClass) . '.class.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
