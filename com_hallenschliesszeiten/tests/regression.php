<?php
// php -d extension=mbstring -d extension=pdo_sqlite tests/regression.php /path/to/Joomla
require __DIR__ . '/joomla6.php';
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Sqlite\SqliteDriver;
use Ttc\Component\Hallenschliesszeiten\Administrator\Table\SchliesszeitTable;
use Ttc\Component\Hallenschliesszeiten\Administrator\Table\HalleninfoTable;
function expect($condition,$message) { if (!$condition) { throw new RuntimeException($message); } }
Factory::$application = new class {
    public $messages=[];
    public $user;
    public function __construct() {
        $this->user=new class {
            public $id=42;
            public $allowed=['core.create','core.edit','core.delete','core.edit.state'];
            public function authorise($action,$asset) { return in_array($action,$this->allowed,true); }
        };
    }
    public function getIdentity() { return $this->user; }
    public function getDispatcher() { static $d; return $d ??= new Joomla\Event\Dispatcher(); }
    public function enqueueMessage($message,$type) { $this->messages[]=$message; }
    public function get($name,$default=null) { return $default; }
    public function getLanguage() { static $l; return $l ??= new Joomla\CMS\Language\Language('en-GB'); }
};
Factory::$language = Factory::$application->getLanguage();
Factory::$language = Factory::$application->getLanguage();
$db = new SqliteDriver(['driver'=>'sqlite','database'=>':memory:','prefix'=>'test_']);
$db->setQuery('CREATE TABLE test_ttc_hallenschliesszeiten (id INTEGER PRIMARY KEY AUTOINCREMENT, state INTEGER DEFAULT 1, ordering INTEGER DEFAULT 0, checked_out INTEGER, checked_out_time TEXT, created_by INTEGER DEFAULT 0, modified_by INTEGER DEFAULT 0, beginn TEXT NOT NULL, ende TEXT NOT NULL, schliessinfo TEXT)')->execute();
$db->setQuery('CREATE TABLE test_ttc_halleninfos (id INTEGER PRIMARY KEY AUTOINCREMENT, state INTEGER DEFAULT 1, ordering INTEGER DEFAULT 0, checked_out INTEGER, checked_out_time TEXT, created_by INTEGER DEFAULT 0, modified_by INTEGER DEFAULT 0, datum TEXT NOT NULL, information TEXT)')->execute();
$t=new SchliesszeitTable($db);
expect($t->bind(['beginn'=>'2026-10-10','ende'=>'2026-10-11','schliessinfo'=>'Ferien','created_by'=>999]),'bind');
expect($t->check() && $t->store(),'valid closure save');
expect($t->created_by===42 && $t->modified_by===42,'author fields from identity');
$t->ende='2026-10-09'; expect(!$t->check(),'reversed dates rejected');
$t->ende='2026-10-12'; $t->beginn='2026-02-30'; expect(!$t->check(),'invalid calendar date rejected');
$t->beginn='2026-10-10'; $t->schliessinfo=str_repeat('x',76); expect(!$t->check(),'schema length enforced');
$i=new HalleninfoTable($db); $i->bind(['datum'=>'2026-10-10','information'=>'Heimspiel']);
expect($i->check() && $i->store(),'valid info save');
$i2=new HalleninfoTable($db); $i2->bind(['datum'=>'2026-10-10','information'=>'Kopie']);
expect(!$i2->check(),'duplicate day rejected');
$i->information='Aktualisiert'; expect($i->check() && $i->store(),'update same day allowed');
$i2->datum='2026-10-12'; $i2->information=str_repeat('x',401); expect(!$i2->check(),'info schema length enforced');
// Exercise actual Joomla 6 normalisation event contract.
$event=new Joomla\CMS\Event\Model\NormaliseRequestDataEvent('onContentNormaliseRequestData', [
 'context'=>'com_hallenschliesszeiten.halleninfo',
 'data'=>(object)['datum'=>'2026-10-10'],
 'subject'=>new Joomla\CMS\Form\Form('test')
]);
expect($event->getData()->datum==='2026-10-10','normalise event');

// Exercise the real frontend save method with a table factory backed by the test database.
class FrontendSaveModel extends \Ttc\Component\Hallenschliesszeiten\Site\Model\HalleninfoformModel {
    public function __construct(private DatabaseInterface $testDatabase) {}
    public function getTable($type='Halleninfo',$prefix='Administrator',$config=[]) { return new HalleninfoTable($this->testDatabase); }
    public function getState($property=null,$default=null) { return $default; }
}
$model=new FrontendSaveModel($db);
Factory::$application->user->allowed=['core.edit.own'];
Factory::$application->user->id=77;
$denied=false;
try { $model->save(['id'=>$i->id,'datum'=>'2026-10-10','information'=>'Fremd']); } catch (Exception $e) { $denied=$e->getCode()===403; }
expect($denied,'edit own rejects another owner');
Factory::$application->user->id=42;
expect($model->save(['id'=>$i->id,'datum'=>'2026-10-10','information'=>'Eigene Info','state'=>-2,'created_by'=>77])===$i->id,'own update allowed');
$stored=new HalleninfoTable($db);$stored->load($i->id);
expect((int)$stored->state===1 && (int)$stored->created_by===42,'state and author cannot be forged');
Factory::$application->user->allowed=[];
$denied=false;
try { $model->save(['id'=>0,'datum'=>'2026-10-12','information'=>'Neue Info']); } catch (Exception $e) { $denied=$e->getCode()===403; }
expect($denied,'create requires component permission');
Factory::$application->user->allowed=['core.create','core.edit','core.delete','core.edit.state'];

// Menu migration updates only old frontend component links.
$db->setQuery('CREATE TABLE test_extensions (extension_id INTEGER, element TEXT, type TEXT)')->execute();
$db->setQuery("INSERT INTO test_extensions VALUES (101, 'com_hallenschliesszeiten', 'component')")->execute();
$db->setQuery('CREATE TABLE test_menu (id INTEGER PRIMARY KEY, link TEXT, client_id INTEGER, type TEXT, component_id INTEGER, alias TEXT, params TEXT, access INTEGER)')->execute();
$db->setQuery("INSERT INTO test_menu VALUES (1, 'index.php?option=com_halleninfos&view=halleninfo&id=7', 0, 'component', 55, 'halle', '{\"item_id\":7}', 3), (2, 'index.php?option=com_content&view=article&id=1', 0, 'component', 9, 'artikel', '{}', 1), (3, 'index.php?option=com_halleninfos&view=halleninfos', 1, 'component', 55, 'backend', '{}', 1)")->execute();
Factory::$container = new Joomla\DI\Container();
Factory::getContainer()->set(DatabaseInterface::class,$db);
require dirname(__DIR__).'/script.php';
$script=new Com_HallenschliesszeitenInstallerScript();
$script->postflight('update',null);
$script->postflight('update',null);
$rows=$db->setQuery('SELECT * FROM test_menu ORDER BY id')->loadObjectList();
expect($rows[0]->link==='index.php?option=com_hallenschliesszeiten&view=halleninfo&id=7' && (int)$rows[0]->component_id===101,'frontend menu migrated');
expect($rows[0]->alias==='halle' && $rows[0]->params==='{"item_id":7}' && (int)$rows[0]->access===3,'menu identity and access retained');
expect($rows[1]->component_id===9 && $rows[2]->component_id===55,'unrelated/backend menus retained');
expect((int)$db->setQuery('SELECT COUNT(*) FROM test_ttc_hallenschliesszeiten')->loadResult()===1,'closure data retained');
expect((int)$db->setQuery('SELECT COUNT(*) FROM test_ttc_halleninfos')->loadResult()===1,'info data retained');
echo "PASS: save, authors, date/length/duplicate validation, Joomla 6 events, idempotent menu migration and existing data.\n";
