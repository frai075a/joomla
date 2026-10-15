<?php
// php tests/joomla6.php /path/to/extracted/Joomla
define('_JEXEC', 1);
$root = $argv[1] ?? '';
if (!is_file($root . '/includes/defines.php')) { throw new RuntimeException('Joomla root required'); }
require $root . '/includes/defines.php';
require JPATH_LIBRARIES . '/loader.php';
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';
$loader->addPsr4('Ttc\\Component\\Spielplanung\\Administrator\\', dirname(__DIR__) . '/administrator/src');
$loader->addPsr4('Ttc\\Component\\Spielplanung\\Site\\', dirname(__DIR__) . '/site/src');
error_reporting(E_ALL);
set_error_handler(static function($level, $message, $file, $line) { throw new ErrorException($message, 0, $level, $file, $line); });
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
echo "PASS: $count component classes load against Joomla " . (new Joomla\CMS\Version())->getShortVersion() . " without compatibility plugins.\n";

$factory = new Joomla\CMS\MVC\Factory\MVCFactory('Ttc\\Component\\Spielplanung');
$resolve = new ReflectionMethod($factory, 'getClassName');
foreach (['Administrator', 'Site'] as $side) {
    $expected = 'Ttc\\Component\\Spielplanung\\' . $side . '\\Model\\SpielplanModel';
    if ($resolve->invoke($factory, 'Model\\SpielplanModel', $side) !== $expected) {
        throw new RuntimeException('Wrong Spielplan model for ' . $side);
    }
}
$manifest = simplexml_load_file(dirname(__DIR__) . '/com_ttc_spielplanung.xml');
$menus = $manifest->xpath('//submenu/menu[@view="spielplaene"]');
if (count($menus) !== 1 || (string) $menus[0]['link'] !== 'option=com_ttc_spielplanung&view=spielplaene') {
    throw new RuntimeException('Missing integrated backend menu');
}
$form = simplexml_load_file(dirname(__DIR__) . '/administrator/forms/spielplan.xml');
if ((string) $form['addfieldprefix'] !== 'Ttc\\Component\\Spielplanung\\Administrator\\Field') {
    throw new RuntimeException('Wrong form field namespace');
}
$assets = json_decode(file_get_contents(dirname(__DIR__) . '/media/joomla.asset.json'), true, 512, JSON_THROW_ON_ERROR);
if ($assets['name'] !== 'com_ttc_spielplanung') {
    throw new RuntimeException('Wrong asset namespace');
}
echo "PASS: integrated menu, form, assets and separate site/admin MVC model resolution.\n";

$installSql = file_get_contents(dirname(__DIR__) . '/administrator/sql/install.mysql.utf8.sql');
$statements = array_values(array_filter(Joomla\Database\DatabaseDriver::splitSql($installSql), static fn($sql) => trim($sql) !== ''));
if (count($statements) !== 4) { throw new RuntimeException('Expected four install tables'); }
foreach ($statements as $statement) {
    if (!preg_match('/^\s*CREATE TABLE IF NOT EXISTS `#__[a-z0-9_]+`\s*\(/', $statement)) {
        throw new RuntimeException('Malformed install table name');
    }
}
$matchSql = trim($statements[3], " \r\n\t;");
foreach (['1.8.0', '1.8.1'] as $version) {
    $updateSql = trim(file_get_contents(dirname(__DIR__) . '/administrator/sql/updates/' . $version . '.sql'), " \r\n\t;");
    if ($matchSql !== $updateSql) { throw new RuntimeException('Install/update table definitions differ: ' . $version); }
}
if (!str_contains($matchSql, '`id` INT UNSIGNED NOT NULL AUTO_INCREMENT')) {
    throw new RuntimeException('Match IDs require auto increment');
}
echo "PASS: installer table names, generated IDs and matching upgrade definitions.\n";

foreach ([
    Ttc\Component\Spielplanung\Administrator\Controller\SpielplanController::class,
    Ttc\Component\Spielplanung\Administrator\Controller\SpielplaeneController::class,
    Ttc\Component\Spielplanung\Administrator\Model\SpielplanModel::class,
    Ttc\Component\Spielplanung\Administrator\Model\SpielplaeneModel::class,
] as $class) {
    $reflection = new ReflectionClass($class);
    $instance = $reflection->newInstanceWithoutConstructor();
    $option = $reflection->getProperty('option')->getValue($instance);
    // Same fallback used by the Joomla controller/model constructors.
    $resolved = $option ?: Joomla\CMS\Component\ComponentHelper::getComponentName($instance, 'spielplan');
    if ($resolved !== 'com_ttc_spielplanung') {
        throw new RuntimeException('Wrong component for redirects/permissions: ' . $class . ': ' . $resolved);
    }
}
$controller = (new ReflectionClass(Ttc\Component\Spielplanung\Administrator\Controller\SpielplanController::class))->newInstanceWithoutConstructor();
$app = new class {
    public array $checked = [];
    public function getIdentity() { return $this; }
    public function authorise($action, $asset) {
        $this->checked[] = [$action, $asset];
        return $asset === 'com_ttc_spielplanung';
    }
    public function getAuthorisedCategories($asset, $action) { return []; }
};
(new ReflectionProperty($controller, 'app'))->setValue($controller, $app);
if (!(new ReflectionMethod($controller, 'allowAdd'))->invoke($controller)
    || $app->checked !== [['core.create', 'com_ttc_spielplanung']]) {
    throw new RuntimeException('New match permission uses the wrong component');
}
echo "PASS: match controllers/models resolve the installed component; native add permission succeeds.\n";
