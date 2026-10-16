<?php
// php tests/joomla6.php /path/to/extracted/Joomla
define('_JEXEC', 1);
$root = $argv[1] ?? '';
if (!is_file($root . '/includes/defines.php')) { throw new RuntimeException('Joomla root required'); }
require $root . '/includes/defines.php';
require JPATH_LIBRARIES . '/loader.php';
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';
$loader->addPsr4('Ttc\\Component\\Hallenschliesszeiten\\Administrator\\', dirname(__DIR__) . '/administrator/src');
$loader->addPsr4('Ttc\\Component\\Hallenschliesszeiten\\Site\\', dirname(__DIR__) . '/site/src');
// Do not restore removed Joomla constants: the component must load without them.
$classesLoaded = false;
register_shutdown_function(static function () use (&$classesLoaded) {
    if (!$classesLoaded) {
        fwrite(STDERR, "FAIL: component class loading terminated before completion.\n");
        exit(1);
    }
});
error_reporting(E_ALL);
set_error_handler(static function($level, $message, $file, $line) { if (!(error_reporting() & $level)) { return false; } if (!(error_reporting() & $level)) { return false; } throw new ErrorException($message, 0, $level, $file, $line); });
$count = 0;
foreach (['administrator', 'site'] as $side) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/' . $side . '/src')) as $file) {
        if ($file->getExtension() !== 'php') { continue; }
        $source = file_get_contents($file->getPathname());
        if (!preg_match('/namespace\s+([^;]+);/', $source, $namespace)
            || !preg_match('/^class\s+(\w+)/m', $source, $class)) { continue; }
        $name = trim($namespace[1]) . '\\' . $class[1];
        if (!class_exists($name)) { throw new RuntimeException('Cannot load ' . $name); }
        $count++;
    }
}
if (class_exists('Joomla\\CMS\\Filesystem\\File', false) || class_exists('JFactory', false)) {
    throw new RuntimeException('Legacy compatibility classes were loaded');
}
$classesLoaded = true;
echo "PASS: $count component classes load against Joomla " . (new Joomla\CMS\Version())->getShortVersion() . " without compatibility plugins.\n";
