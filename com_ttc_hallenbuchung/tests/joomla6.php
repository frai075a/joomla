<?php
// php -d extension=mbstring -d extension=pdo_sqlite -d extension=sqlite3 tests/joomla6.php /path/to/extracted/Joomla
define('_JEXEC', 1);
$completed = false;
register_shutdown_function(static function () use (&$completed) {
    if (!$completed) { fwrite(STDERR, "FAIL: test stopped before completion (possible silent die).\n"); exit(1); }
});
$root = $argv[1] ?? '';
if (!is_file($root . '/includes/defines.php')) { throw new RuntimeException('Joomla root required'); }
define('JPATH_BASE', realpath($root));
require $root . '/includes/defines.php';
if (defined('JPATH_PLATFORM')) { throw new RuntimeException('Test requires native Joomla bootstrap without JPATH_PLATFORM'); }
require JPATH_LIBRARIES . '/loader.php';
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';
$loader->addPsr4('Ttchallenbuchung\\Component\\Ttc_hallenbuchung\\Administrator\\', dirname(__DIR__) . '/administrator/src');
$loader->addPsr4('Ttchallenbuchung\\Component\\Ttc_hallenbuchung\\Site\\', dirname(__DIR__) . '/site/src');
error_reporting(E_ALL);
set_error_handler(static function($level, $message, $file, $line) { if (!(error_reporting() & $level)) { return false; } throw new ErrorException($message, 0, $level, $file, $line); });
$count = 0;
foreach (['administrator', 'site'] as $side) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/' . $side . '/src')) as $file) {
        if ($file->getExtension() !== 'php') { continue; }
        $source = file_get_contents($file->getPathname());
        if (!preg_match('/namespace\s+([^;]+);/', $source, $namespace)
            || !preg_match('/^\s*class\s+(\w+)/m', $source, $class)) { continue; }
        $name = trim($namespace[1]) . '\\' . $class[1];
        if (!class_exists($name)) { throw new RuntimeException('Cannot load ' . $name); }
        $count++;
    }
}
if (class_exists('Joomla\\CMS\\Filesystem\\File', false) || class_exists('JFactory', false)) {
    throw new RuntimeException('Legacy compatibility classes were loaded');
}
echo "PASS: $count component classes load against Joomla " . (new Joomla\CMS\Version())->getShortVersion() . " without compatibility plugins.\n";

class TestSqliteDriver extends Joomla\Database\Sqlite\SqliteDriver {
    public function insertObject($table, &$object, $key = null) {
        // Match MySQL AUTO_INCREMENT for zero primary keys.
        if ($key && isset($object->$key) && (int) $object->$key === 0) { $object->$key = null; }
        return parent::insertObject($table, $object, $key);
    }
}
$db = new TestSqliteDriver(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => 'test_']);
$db->setQuery('CREATE TABLE test_ttc_hallenfussball (id INTEGER PRIMARY KEY AUTOINCREMENT, created_by INTEGER DEFAULT 0, created_when TEXT, datum TEXT, teilnehmer TEXT, zusage TEXT)')->execute();
$db->setQuery('CREATE TABLE test_users (id INTEGER PRIMARY KEY, name TEXT)')->execute();
$dispatcher = new Joomla\Event\Dispatcher();
$container = Joomla\CMS\Factory::getContainer()->createChild();
Joomla\CMS\Factory::$container = $container;
$container->set(Joomla\Event\DispatcherInterface::class, $dispatcher);
$container->set(Joomla\Database\DatabaseInterface::class, $db);
$user = new class extends Joomla\CMS\User\User {
    public $allowed = null;
    public function authorise($action, $assetname = null) { return $this->allowed === null || in_array($action, $this->allowed, true); }
};
$user->id = 42;
Joomla\CMS\Factory::$language = new Joomla\CMS\Language\Language('en-GB');
Joomla\CMS\Factory::$application = new class($user) {
    public $input; public $states = []; public $config = [];
    public function __construct(private $user) { $this->input = new Joomla\Input\Input(); }
    public function getDispatcher() { return Joomla\CMS\Factory::getContainer()->get(Joomla\Event\DispatcherInterface::class); }
    public function getLanguage() { return Joomla\CMS\Factory::$language; }
    public function getUserState($key, $default = null) { return $this->states[$key] ?? $default; }
    public function getIdentity() { return $this->user; }
    public function getInput() { return $this->input; }
    public function get($key, $default = null) { return $this->config[$key] ?? $default; }
};
$table = new Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Table\HallenfussballteilnahmeTable($db);
if (!$table->save(['datum' => '2999-10-14', 'teilnehmer' => 'Testspieler', 'zusage' => 'ja'])) {
    throw new RuntimeException('Insert failed');
}
$id = $table->id;
if ((int) $table->created_by !== 42 || !$id) { throw new RuntimeException('Creator or ID missing'); }
if (!$table->save(['id' => $id, 'datum' => '2999-10-14', 'teilnehmer' => 'Testspieler', 'zusage' => 'nein'])) {
    throw new RuntimeException('Update failed');
}
$db->setQuery('SELECT zusage FROM test_ttc_hallenfussball WHERE id=' . (int) $id);
if ($db->loadResult() !== 'nein') { throw new RuntimeException('Update not stored'); }
$factory = new Joomla\CMS\MVC\Factory\MVCFactory('Ttchallenbuchung\\Component\\Ttc_hallenbuchung');
$factory->setDatabase($db);
$factory->setDispatcher($dispatcher);
foreach (['Administrator', 'Site'] as $side) {
    $model = $factory->createModel('Hallenfussballteilnehmer', $side, ['ignore_request' => true]);
    if ((new ReflectionMethod($model, 'getMVCFactory'))->invoke($model) !== $factory) { throw new RuntimeException('Injected MVC factory lost'); }
    $model->setState('list.ordering', 'a.datum');
    $model->setState('list.direction', 'ASC');
    $model->setState('filter.search', '');
    $query = new ReflectionMethod($model, 'getListQuery');
    $db->setQuery($query->invoke($model));
    if (count($db->loadObjectList()) !== 1) { throw new RuntimeException('List query failed'); }
}
if (!$table->delete($id)) { throw new RuntimeException('Delete failed'); }
$db->setQuery('SELECT COUNT(*) FROM test_ttc_hallenfussball');
if ((int) $db->loadResult() !== 0) { throw new RuntimeException('Record not deleted'); }
$form = new Joomla\CMS\Form\Form('test');
$form->load('<form><field name="teilnehmer" type="text"/></form>');
$data = (object) ['teilnehmer' => 'before'];
$dispatcher->addListener('onContentNormaliseRequestData', static function ($event) { $event->getData()->teilnehmer = 'after'; });
$dispatcher->dispatch('onContentNormaliseRequestData', new Joomla\CMS\Event\Model\NormaliseRequestDataEvent('onContentNormaliseRequestData', ['context' => 'com_ttc_hallenbuchung.hallenfussballteilnahme', 'data' => $data, 'subject' => $form]));
if ($data->teilnehmer !== 'after') { throw new RuntimeException('Normalisation event failed'); }
echo "PASS: insert (current creator), update, delete, frontend/backend SQL, MVC factory injection and typed form event.\n";

$provider = require (dirname(__DIR__) . '/administrator/services/provider.php');
$componentContainer = $container->createChild();
$provider->register($componentContainer);
$componentContainer->set(Joomla\CMS\MVC\Factory\MVCFactoryInterface::class, $factory, true);
$component = $componentContainer->get(Joomla\CMS\Extension\ComponentInterface::class);
if ($component->getMVCFactory() !== $factory || !($component instanceof Joomla\CMS\Component\Router\RouterServiceInterface)) {
    throw new RuntimeException('Component provider failed');
}
echo "PASS: component service provider and router registration.\n";

$adminForm = new Joomla\CMS\Form\Form('com_ttc_hallenbuchung.hallenfussballteilnahme');
$adminForm->loadFile((dirname(__DIR__) . '/administrator/forms/hallenfussballteilnahme.xml'));
$filtered = $adminForm->filter(['datum' => '2999-10-14', 'teilnehmer' => 'Testspieler', 'zusage' => 'ja']);
if ($filtered['datum'] !== '2999-10-14') { throw new RuntimeException('Calendar filter failed'); }
echo "PASS: backend calendar form filtering.\n";

$siteInput = new Joomla\Input\Input(['option' => 'com_ttc_hallenbuchung', 'view' => 'hallenfussballteilnehmer']);
$siteApp = new Joomla\CMS\Application\SiteApplication($siteInput, new Joomla\Registry\Registry(['session' => false]), null, $componentContainer);
$siteApp->loadLanguage(Joomla\CMS\Factory::$language);
$dispatcherFactory = $componentContainer->get(Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface::class);
$siteDispatcher = $dispatcherFactory->createDispatcher($siteApp, $siteInput);
if (!$siteDispatcher instanceof Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Dispatcher\Dispatcher) {
    throw new RuntimeException('Frontend dispatcher was not created');
}
echo "PASS: frontend dispatcher created without JPATH_PLATFORM.\n";

$frontendForm = new Joomla\CMS\Form\Form('frontend', ['control' => 'jform']);
$frontendForm->loadFile(dirname(__DIR__) . '/site/forms/hallenfussballteilnahmeform.xml');
foreach (['14.10.2026' => '2026-10-14', '29.02.2028' => '2028-02-29', '31.02.2026' => '', '2026-10-14' => '2026-10-14'] as $inputDate => $expected) {
    $filtered = $frontendForm->filter(['datum' => $inputDate, 'teilnehmer' => 'Test', 'zusage' => 'ja']);
    if ($filtered['datum'] !== $expected) { throw new RuntimeException('German date conversion failed: ' . $inputDate); }
}
if ($frontendForm->getField('datum')->format !== '%d.%m.%Y' || $frontendForm->getField('id')->type !== 'Hidden') { throw new RuntimeException('Form format or record number visibility incorrect'); }
$container->set(Joomla\CMS\User\UserFactoryInterface::class, new class implements Joomla\CMS\User\UserFactoryInterface {
    public function loadUserById(int $id): Joomla\CMS\User\User {
        $result = new Joomla\CMS\User\User();
        if ($id === 84) { $result->id = 84; $result->username = 'other-user'; $result->name = 'Other, Player'; }
        return $result;
    }
    public function loadUserByUsername(string $username): Joomla\CMS\User\User {
        return $this->loadUserById($username === 'other-user' ? 84 : 0);
    }
}, true);
$user->username = 'austho5';
$user->name = 'Austen, Thorsten';
$formModel = new class(['ignore_request' => true], $factory) extends Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Model\HallenfussballteilnahmeformModel {
    public $sample = ['id' => 0, 'teilnehmer' => ''];
    public function getItem($id = null) { return (object) $this->sample; }
};
$loadData = new ReflectionMethod($formModel, 'loadFormData');
if ($loadData->invoke($formModel)->teilnehmer !== 'Austen, Thorsten') { throw new RuntimeException('Display name default missing'); }
$formModel->sample = ['id' => 7, 'teilnehmer' => 'Existing player'];
if ($loadData->invoke($formModel)->teilnehmer !== 'Existing player') { throw new RuntimeException('Existing participant overwritten'); }
Joomla\CMS\Factory::$application->states['com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data'] = ['id' => 0, 'teilnehmer' => 'Entered player'];
if ($loadData->invoke($formModel)['teilnehmer'] !== 'Entered player') { throw new RuntimeException('Submitted participant overwritten'); }
echo "PASS: German dates, hidden record ID and display name defaults (new, existing, submitted).\n";

$app = Joomla\CMS\Factory::$application;
$stateKey = 'com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data';
$app->states = [];
$formModel->sample = ['id' => 7, 'teilnehmer' => '42'];
if ($loadData->invoke($formModel)->teilnehmer !== 'Austen, Thorsten') { throw new RuntimeException('Stored current user ID not resolved'); }
$app->states[$stateKey] = ['id' => 0, 'teilnehmer' => '42'];
if ($loadData->invoke($formModel)['teilnehmer'] !== 'Austen, Thorsten') { throw new RuntimeException('Session user ID not resolved'); }

$app->states = [];
$formModel->sample = ['id' => 7, 'teilnehmer' => '84'];
if ($loadData->invoke($formModel)->teilnehmer !== 'Other, Player') { throw new RuntimeException('Other participant user ID not resolved'); }
$formModel->sample = ['id' => 7, 'teilnehmer' => '99999'];
if ($loadData->invoke($formModel)->teilnehmer !== '99999') { throw new RuntimeException('Unknown participant changed'); }
echo "PASS: participant IDs resolve to display names in records and form sessions.\n";

$app->states = [];
$formModel->sample = ['id' => 7, 'teilnehmer' => 'austho5'];
if ($loadData->invoke($formModel)->teilnehmer !== 'Austen, Thorsten') { throw new RuntimeException('Stored login name not resolved'); }
$app->states[$stateKey] = ['id' => 0, 'teilnehmer' => 'austho5'];
if ($loadData->invoke($formModel)['teilnehmer'] !== 'Austen, Thorsten') { throw new RuntimeException('Cached login name not resolved'); }
$app->states = [];
$formModel->sample = ['id' => 7, 'teilnehmer' => 'other-user'];
if ($loadData->invoke($formModel)->teilnehmer !== 'Other, Player') { throw new RuntimeException('Other login name not resolved'); }
echo "PASS: austho5 resolves to Austen, Thorsten in stored records and cached defaults.\n";

// The legacy table is used in place: existing IDs, ownership and values must survive.
$db->setQuery('CREATE TABLE test_ttc_coronaorga_ (id INTEGER PRIMARY KEY AUTOINCREMENT, ordering INTEGER NOT NULL, state INTEGER NOT NULL DEFAULT 1, created_by INTEGER NOT NULL, modified_by INTEGER NOT NULL, datum TEXT NOT NULL, slot TEXT NOT NULL, paarung TEXT NOT NULL)')->execute();
$db->setQuery("INSERT INTO test_ttc_coronaorga_ VALUES (101, 1, 1, 42, 42, '2999-10-14 00:00:00', '1', 'Existing pair')")->execute();
$training = $factory->createModel('Tttrainingform', 'Site', ['ignore_request' => true]);
$legacy = $training->getItem(101);
if ($legacy->paarung !== 'Existing pair' || (int) $legacy->id !== 101) { throw new RuntimeException('Existing training booking not reused'); }
if ($training->getItem(0)->paarung !== 'Austen, Thorsten') { throw new RuntimeException('Training display name default failed'); }
$ids = [101];
for ($i = 2; $i <= 5; $i++) {
    $id = $training->save(['id' => 0, 'datum' => '2999-10-14', 'slot' => '1', 'paarung' => 'Pair ' . $i,
        'created_by' => 999, 'state' => 0]);
    if (!$id) { throw new RuntimeException('Training booking failed: ' . $training->getError()); }
    $ids[] = $id;
    $row = $training->getItem($id);
    if ((int) $row->created_by !== 42 || (int) $row->state !== 1) { throw new RuntimeException('Frontend ownership/state tampering accepted'); }
}
if ($training->save(['datum' => '2999-10-14', 'slot' => '1', 'paarung' => 'Sixth pair']) !== false) {
    throw new RuntimeException('Slot capacity exceeded');
}
if (!$training->save(['id' => 101, 'datum' => '2999-10-14', 'slot' => '1', 'paarung' => 'Edited pair'])) {
    throw new RuntimeException('Own existing booking counted twice');
}
$otherSlot = $training->save(['datum' => '2999-10-14', 'slot' => '2', 'paarung' => 'Other slot']);
if (!$otherSlot) { throw new RuntimeException('Independent slot blocked'); }
$table = $factory->createTable('Tttraining', 'Administrator');
if (!$table->save(['datum' => '2999-10-14', 'slot' => '1', 'paarung' => 'Unpublished sixth', 'state' => 0])) {
    throw new RuntimeException('Unpublished booking failed');
}
$unpublishedId = $table->id;
if ($table->publish([$unpublishedId], 1)) { throw new RuntimeException('Publishing bypasses slot limit'); }
$table->load($unpublishedId);
if ((int) $table->state !== 0) { throw new RuntimeException('Rejected publication changed database'); }
foreach (['31.02.2026', '2026-02-31'] as $badDate) {
    if ($training->save(['datum' => $badDate, 'slot' => '2', 'paarung' => 'Invalid']) !== false) { throw new RuntimeException('Invalid date accepted'); }
}
if ($training->save(['datum' => '2999-10-14', 'slot' => '3', 'paarung' => 'Invalid']) !== false) { throw new RuntimeException('Invalid slot accepted'); }
if ($training->save(['datum' => '2999-10-14', 'slot' => '2', 'paarung' => str_repeat('x', 101)]) !== false) { throw new RuntimeException('Oversized pairing accepted'); }

$user->allowed = ['core.create', 'core.edit.own'];
if (!$training->getItem(101)) { throw new RuntimeException('Own edit denied'); }
$db->setQuery('UPDATE test_ttc_coronaorga_ SET created_by=84 WHERE id=' . (int) $otherSlot)->execute();
foreach (['read', 'save', 'delete'] as $operation) {
    try {
        if ($operation === 'read') { $training->getItem($otherSlot); }
        elseif ($operation === 'save') { $training->save(['id' => $otherSlot, 'datum' => '2999-10-14', 'slot' => '2', 'paarung' => 'Stolen']); }
        else { $training->delete($otherSlot); }
        throw new RuntimeException('Unauthorized operation accepted: ' . $operation);
    } catch (RuntimeException $error) {
        if ($error->getCode() !== 403) { throw $error; }
    }
}
$db->setQuery("INSERT INTO test_ttc_coronaorga_ VALUES (201, 20, 1, 42, 42, '2000-01-01 00:00:00', '1', 'Past pair')")->execute();
foreach (['Administrator' => 8, 'Site' => 6] as $side => $expectedCount) {
    $model = $factory->createModel('Tttrainings', $side, ['ignore_request' => true]);
    $model->setState('filter.search', '');
    $model->setState('list.ordering', 'a.datum');
    $model->setState('list.direction', 'ASC');
    $db->setQuery((new ReflectionMethod($model, 'getListQuery'))->invoke($model));
    if (count($db->loadObjectList()) !== $expectedCount) { throw new RuntimeException('Training list visibility failed: ' . $side); }
    $model->setState('filter.search', 'Edited');
    $model->setState('list.ordering', 'a.datum; DROP TABLE test_ttc_coronaorga_');
    $model->setState('list.direction', 'ASC; DROP TABLE test_ttc_coronaorga_');
    $db->setQuery((new ReflectionMethod($model, 'getListQuery'))->invoke($model));
    if (count($db->loadObjectList()) !== 1) { throw new RuntimeException('Training search/order filtering failed'); }
}
$trainingForm = new Joomla\CMS\Form\Form('training');
$trainingForm->loadFile(dirname(__DIR__) . '/site/forms/tttrainingform.xml');
$values = $trainingForm->filter(['datum' => '14.10.2026', 'slot' => '2', 'paarung' => '<script>Test</script>']);
if ($values['datum'] !== '2026-10-14' || str_contains($values['paarung'], '<script>')) { throw new RuntimeException('Training form filtering failed'); }
$adminTraining = $factory->createModel('Tttraining', 'Administrator', ['ignore_request' => true]);
if ((int) $adminTraining->getItem(101)->id !== 101) { throw new RuntimeException('Backend training item cannot load'); }
$user->allowed = null;
if (!$training->delete($otherSlot)) { throw new RuntimeException('Authorized training delete failed'); }
try { $training->getItem($otherSlot); throw new RuntimeException('Deleted booking still available'); }
catch (RuntimeException $error) { if ($error->getCode() !== 404) { throw $error; } }
$user->id = 0;
try { $training->getItem(0); throw new RuntimeException('Guest booking accepted'); }
catch (RuntimeException $error) { if ($error->getCode() !== 403) { throw $error; } }
$user->id = 42;
echo "PASS: TT training reuses legacy data; CRUD, German form dates, five-pair capacity, publication, ownership/guest checks and frontend/backend visibility.\n";


// Frontend date boundaries use the configured site timezone for every role.
foreach (['Europe/Berlin', 'Pacific/Kiritimati', 'Pacific/Pago_Pago'] as $zone) {
    $app->config['offset'] = $zone;
    $today = Joomla\CMS\Factory::getDate('now', new DateTimeZone($zone))->format('Y-m-d', true);
    $yesterday = (new DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d');
    $tomorrow = (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
    $db->setQuery('DELETE FROM test_ttc_hallenfussball')->execute();
    $db->setQuery('DELETE FROM test_ttc_coronaorga_')->execute();
    foreach ([$yesterday . ' 23:59:59', $today . ' 00:00:00', $today . ' 23:59:59', $tomorrow . ' 00:00:00'] as $i => $date) {
        $db->setQuery("INSERT INTO test_ttc_hallenfussball (id, created_by, datum, teilnehmer, zusage) VALUES ("
            . ($i + 1) . ",42," . $db->quote($date) . ",'Boundary player','ja')")->execute();
        $db->setQuery("INSERT INTO test_ttc_coronaorga_ VALUES (" . ($i + 1) . ",1,1,42,42,"
            . $db->quote($date) . ",'1','Boundary player')")->execute();
    }
    foreach ([[], ['core.edit']] as $permissions) {
        $user->allowed = $permissions;
        foreach (['Hallenfussballteilnehmer', 'Tttrainings'] as $name) {
            $model = $factory->createModel($name, 'Site', ['ignore_request' => true]);
            $model->setState('filter.search', 'Boundary');
            $model->setState('list.ordering', 'a.datum');
            $model->setState('list.direction', 'ASC');
            $query = (new ReflectionMethod($model, 'getListQuery'))->invoke($model);
            if (str_contains((string) $query, 'CURRENT_DATE') || !str_contains((string) $query, $db->quote($today))) {
                throw new RuntimeException('Site timezone ignored: ' . $name);
            }
            $db->setQuery($query);
            $visible = array_map('intval', $db->loadColumn());
            if ($visible !== [2, 3, 4]) { throw new RuntimeException('Frontend yesterday/today/tomorrow boundary failed: ' . $name . ' / ' . $zone); }
        }
    }
    foreach (['Hallenfussballteilnehmer', 'Tttrainings'] as $name) {
        $model = $factory->createModel($name, 'Administrator', ['ignore_request' => true]);
        $model->setState('filter.search', 'Boundary');
        $model->setState('list.ordering', 'a.datum');
        $model->setState('list.direction', 'ASC');
        $db->setQuery((new ReflectionMethod($model, 'getListQuery'))->invoke($model));
        if (count($db->loadObjectList()) !== 4) { throw new RuntimeException('Backend history was filtered: ' . $name); }
    }
}
$user->allowed = null;
$app->config = [];
echo "PASS: both frontend lists exclude yesterday and include all of today/tomorrow in three site timezones, including frontend editors; backend history remains available.\n";


// Installing under the renamed identifier migrates links/ACL/settings, not bookings.
$db->setQuery('CREATE TABLE test_extensions (extension_id INTEGER PRIMARY KEY, type TEXT, element TEXT, params TEXT)')->execute();
$db->setQuery('CREATE TABLE test_menu (id INTEGER PRIMARY KEY, client_id INTEGER, type TEXT, link TEXT, component_id INTEGER)')->execute();
$db->setQuery('CREATE TABLE test_assets (id INTEGER PRIMARY KEY, name TEXT, rules TEXT)')->execute();
$legacyOption = 'com_ttc_' . 'hallenfussball';
$db->setQuery("INSERT INTO test_extensions VALUES (10,'component'," . $db->quote($legacyOption)
    . ",'{\"legacy_setting\":1}'),(20,'component','com_ttc_hallenbuchung','{}')")->execute();
$rules = '{"core.create":{"8":1},"core.edit.own":{"2":1}}';
$db->setQuery("INSERT INTO test_assets VALUES (1," . $db->quote($legacyOption) . ',' . $db->quote($rules)
    . "),(2,'com_ttc_hallenbuchung','{}')")->execute();
$links = [
    1 => 'index.php?option=' . $legacyOption . '&view=hallenfussballteilnehmer',
    2 => 'index.php?option=' . $legacyOption . '&view=tttrainings&Itemid=7',
    3 => 'index.php?option=' . $legacyOption . '_other&view=anything',
    4 => 'index.php?option=com_content&view=article&id=12',
    5 => 'index.php?view=tttrainingform&option=' . $legacyOption,
];
foreach ($links as $id => $link) {
    $db->setQuery("INSERT INTO test_menu VALUES ($id,0,'component'," . $db->quote($link) . ',10)')->execute();
}
$db->setQuery("INSERT INTO test_menu VALUES (6,1,'component'," . $db->quote($links[1]) . ',10)')->execute();
require dirname(__DIR__) . '/script.php';
$installer = new com_ttc_hallenbuchungInstallerScript();
if (!$installer->postflight('install', null)) { throw new RuntimeException('Renamed component installation failed'); }
$db->setQuery('SELECT * FROM test_menu ORDER BY id');
foreach ($db->loadObjectList() as $menu) {
    if (in_array((int) $menu->id, [1, 2, 5], true)) {
        $expected = str_replace($legacyOption, 'com_ttc_hallenbuchung', $links[$menu->id]);
        if ($menu->link !== $expected || (int) $menu->component_id !== 20) { throw new RuntimeException('Frontend menu not migrated'); }
    } elseif ($menu->link !== $links[$menu->id === 6 ? 1 : $menu->id] || (int) $menu->component_id !== 10) {
        throw new RuntimeException('Unrelated or administrator menu changed');
    }
}
$db->setQuery("SELECT rules FROM test_assets WHERE name='com_ttc_hallenbuchung'");
if ($db->loadResult() !== $rules) { throw new RuntimeException('Component ACL not migrated'); }
$db->setQuery("SELECT params FROM test_extensions WHERE element='com_ttc_hallenbuchung'");
if ($db->loadResult() !== '{"legacy_setting":1}') { throw new RuntimeException('Component settings not migrated'); }
$db->setQuery("UPDATE test_assets SET rules='{}' WHERE name='com_ttc_hallenbuchung'")->execute();
$db->setQuery("UPDATE test_extensions SET params='{}' WHERE element='com_ttc_hallenbuchung'")->execute();
$installer->postflight('update', null);
$db->setQuery("SELECT rules FROM test_assets WHERE name='com_ttc_hallenbuchung'");
if ($db->loadResult() !== '{}') { throw new RuntimeException('Update overwrote current permissions'); }
$db->setQuery("SELECT params FROM test_extensions WHERE element='com_ttc_hallenbuchung'");
if ($db->loadResult() !== '{}') { throw new RuntimeException('Update overwrote current settings'); }
foreach (['test_ttc_hallenfussball', 'test_ttc_coronaorga_'] as $tableName) {
    $db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName($tableName));
    if ((int) $db->loadResult() !== 4) { throw new RuntimeException('Rename changed bookings'); }
}
echo "PASS: renamed component installer migrates exact frontend links, ACL/settings and preserves current configuration on update; shared booking data retained.\n";

$completed = true;



