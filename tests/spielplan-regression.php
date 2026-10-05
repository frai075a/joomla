<?php
/** Standalone checks: php -d extension=mbstring -d extension=pdo_sqlite tests/regression.php */
namespace Joomla\CMS\MVC\Factory { interface MVCFactoryInterface {} }
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $data = []) {}
        public function get($key, $default = null) { return $this->data[$key] ?? $default; }
        public function set($key, $value) { $this->data[$key] = $value; }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _($key) { return $key; }
        public static function sprintf($key, ...$args) { return $key . ': ' . implode(', ', $args); }
    }
}
namespace Joomla\CMS {
    class Factory {
        public static $db;
        public static $user;
        public static $app;
        public static function getApplication() { return self::$app; }
        public static function getDate($value = 'now') { return new \DateTimeImmutable($value); }
    }
}
namespace Joomla\CMS\MVC\Model {
    class ListModel {
        public $state;
        public $factory;
        protected $filter_fields;
        protected $context = 'com_ttc_spielplanung.spielplaene';
        public function __construct($config = [], $factory = null) {
            $this->state = new \Joomla\Registry\Registry();
            $this->filter_fields = $config['filter_fields'] ?? [];
            $this->factory = $factory;
        }
        public function getDatabase() { return \Joomla\CMS\Factory::$db; }
        public function getState($key, $default = null) { return $this->state->get($key, $default); }
        public function setState($key, $value) { $this->state->set($key, $value); }
        public function cleanCache() {}
        protected function getStoreId($id = '') { return $id; }
        protected function populateState($order = null, $direction = null) {}
        public function getUserStateFromRequest($key, $request) { return \Joomla\CMS\Factory::$app->requests[$request] ?? ''; }
    }
    class AdminModel extends ListModel {
        public $failSave = false;
        public function loadForm(...$args) { return false; }
        public function getItem($id = null) {
            $db = $this->getDatabase();
            $db->setQuery('SELECT * FROM ttc_spielplan WHERE id=' . (int) $id);
            return $db->pdo->query($db->query)->fetchObject();
        }
        public function save($data) {
            if ($this->failSave) { return false; }
            unset($data['id']);
            $this->getDatabase()->insertObject('#__ttc_spielplan', (object) $data);
            return true;
        }
        public function getError() { return 'Plugin veto'; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class AdminController {
        public $input;
        public $redirect;
        public function __construct() { $this->input = \Joomla\CMS\Factory::$app->input; }
        public function checkToken() {
            if (!\Joomla\CMS\Factory::$app->token) { throw new \RuntimeException('CSRF'); }
        }
        public function getModel($name = 'Spielplan', $prefix = '', $config = []) {
            $class = '\\Ttc\\Component\\Spielplanung\\Administrator\\Model\\' . $name . 'Model';
            return new $class();
        }
        public function setRedirect($url) { $this->redirect = $url; }
    }
}
namespace {
    define('_JEXEC', 1);
    error_reporting(E_ALL);
    set_error_handler(static function($severity, $message, $file, $line) { throw new \ErrorException($message, 0, $severity, $file, $line); });
    use Joomla\CMS\Factory;
    use Ttc\Component\Spielplanung\Administrator\Model\SpielplaeneModel;
    use Ttc\Component\Spielplanung\Administrator\Model\SpielplanModel;
    use Ttc\Component\Spielplanung\Administrator\Service\SpielplanCsvReader;
    require __DIR__ . '/../administrator/src/Service/SpielplanCsvReader.php';
    require __DIR__ . '/../administrator/src/Model/SpielplaeneModel.php';
    require __DIR__ . '/../administrator/src/Model/SpielplanModel.php';
    require __DIR__ . '/../administrator/src/Controller/SpielplaeneController.php';

    class Query {
        private $sql = '';
        private $conditions = [];
        private $ordering = '';
        public function select($fields) { $this->sql = 'SELECT ' . $fields; return $this; }
        public function from($table) { $this->sql .= ' FROM ' . $table; return $this; }
        public function delete($table) { $this->sql = 'DELETE FROM ' . $table; return $this; }
        public function where($condition) { $this->conditions[] = $condition; return $this; }
        public function order($order) { $this->ordering = $order; return $this; }
        public function __toString() {
            return $this->sql . ($this->conditions ? ' WHERE ' . implode(' AND ', $this->conditions) : '')
                . ($this->ordering ? ' ORDER BY ' . $this->ordering : '');
        }
    }
    class Database {
        public $pdo;
        public $query;
        public $writes = 0;
        public $failAt = 0;
        private $affected = 0;
        public function __construct() {
            $this->pdo = new \PDO('sqlite::memory:');
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('CREATE TABLE ttc_spielplan (id INTEGER PRIMARY KEY, mannschaft INTEGER, datum TEXT, uhrzeit TEXT, heimmannschaft TEXT, h_nummer TEXT, auswaertsmannschaft TEXT, a_nummer TEXT, ort TEXT, hallennr TEXT, ort_key INTEGER)');
        }
        public function getQuery($new) { return new Query(); }
        public function setQuery($query) { $this->query = str_replace('#__', '', (string) $query); return $this; }
        public function quoteName($name) { return '"' . str_replace(['#__', '.'], ['', '"."'], $name) . '"'; }
        public function quote($value) { return $this->pdo->quote($value); }
        public function escape($value, $extra = false) { return $value; }
        public function execute() { $this->affected = $this->pdo->exec($this->query); }
        public function getAffectedRows() { return $this->affected; }
        public function transactionStart() { $this->pdo->beginTransaction(); }
        public function transactionCommit() { $this->pdo->commit(); }
        public function transactionRollback() { $this->pdo->rollBack(); }
        public function insertObject($table, $record) {
            if (++$this->writes === $this->failAt) { throw new \RuntimeException('Write failed'); }
            $values = (array) $record;
            $statement = $this->pdo->prepare('INSERT INTO ' . str_replace('#__', '', $table)
                . '(' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')');
            $statement->execute(array_values($values));
        }
    }
    function fixture() {
        Factory::$db = new Database();
        Factory::$user = new class {
            public $denied = [];
            public function authorise($action, $asset) { return !in_array($action, $this->denied, true); }
        };
        Factory::$app = new class {
            public $token = true;
            public $requests = [];
            public $input;
            public function __construct() {
                $this->input = new class {
                    public $post;
                    public $files;
                    public function __construct() { $this->post = $this; $this->files = $this; }
                    public function get($key, $default = null, $filter = '') { return $default; }
                };
            }
            public function getIdentity() { return Factory::$user; }
            public function enqueueMessage($message, $type = '') {}
        };
    }
    function check($condition) { if (!$condition) { throw new \RuntimeException('Assertion failed'); } }
    function rejects($callback) {
        try { $callback(); } catch (\Exception $e) { return; }
        throw new \RuntimeException('Expected exception');
    }
    $tests = 0;
    function test($name, $callback) { global $tests; fixture(); $callback(); $tests++; echo "PASS: $name\n"; }
    function csv($rows = null, $bom = false) {
        $header = "Termin;HeimVereinName;HeimMannschaftNr;GastVereinName;GastMannschaftNr;HalleName;HalleStrasse;HallePLZ;HalleOrt\n";
        $rows ??= "05.10.2026 20:15;TTC Nordend Frankfurt;2;Gast;3;Halle;Straße;60318;Frankfurt\n";
        return 'data://text/plain;base64,' . base64_encode(($bom ? "\xEF\xBB\xBF" : '') . $header . $rows);
    }
    function countRows() { return (int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_spielplan')->fetchColumn(); }
    test('CSV reads UTF-8 BOM, dates, roman numbers and venue', function() {
        $row = (new SpielplanCsvReader())->read(csv(null, true))[0];
        check($row->datum === '2026-10-05' && $row->uhrzeit === '20:15:00' && $row->mannschaft === 2);
        check($row->h_nummer === 'II' && $row->a_nummer === 'III');
        check($row->ort === 'Halle, Straße, 60318 Frankfurt');
    });
    test('CSV supports Windows-1252 and quoted semicolons', function() {
        $path = csv('05.10.2026 20:15;Gast;1;TTC Nordend Frankfurt;4;"Halle; Süd";Straße;60318;Frankfurt');
        $bytes = base64_decode(substr($path, strpos($path, ',') + 1));
        $path = 'data://text/plain;base64,' . base64_encode(mb_convert_encoding($bytes, 'Windows-1252', 'UTF-8'));
        $row = (new SpielplanCsvReader())->read($path)[0];
        check($row->mannschaft === 4 && $row->a_nummer === 'IV' && str_contains($row->ort, 'Halle; Süd'));
    });
    test('Invalid CSV rows are rejected before writes', function() {
        foreach (['31.02.2026 20:15;TTC Nordend Frankfurt;1;Gast;1;H;S;P;O',
            '05.10.2026 20:15;TTC Nordend Frankfurt;14;Gast;1;H;S;P;O', 'short;row'] as $row) {
            rejects(function() use ($row) { (new SpielplaeneModel())->importSpielplan(csv($row)); });
        }
        check(countRows() === 0 && Factory::$db->writes === 0);
    });
    test('Missing headers and empty CSV are rejected', function() {
        foreach (['', 'wrong;header', "Termin\n"] as $value) {
            rejects(function() use ($value) { (new SpielplanCsvReader())->read('data://text/plain;base64,' . base64_encode($value)); });
        }
    });
    test('Import saves into prefixed table without DDL', function() {
        check((new SpielplaeneModel())->importSpielplan(csv()) === 1);
        check(countRows() === 1);
        check(Factory::$db->pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table'")->fetchColumn() === 1);
    });
    test('Mid-import write failure rolls all rows back', function() {
        Factory::$db->failAt = 2;
        $row = "05.10.2026 20:15;TTC Nordend Frankfurt;2;Gast;3;H;S;P;O\n";
        rejects(function() use ($row) { (new SpielplaeneModel())->importSpielplan(csv($row . $row)); });
        check(countRows() === 0);
    });
    test('Import and bulk delete enforce model permissions', function() {
        foreach (['core.manage', 'core.create'] as $action) {
            Factory::$user->denied = [$action];
            rejects(function() { (new SpielplaeneModel())->importSpielplan(csv()); });
        }
        foreach (['core.manage', 'core.delete'] as $action) {
            Factory::$user->denied = [$action];
            rejects(function() { (new SpielplaeneModel())->deleteSpielplan(); });
        }
        check(Factory::$db->writes === 0);
    });
    test('Bulk delete preserves past dates and non-team rows', function() {
        Factory::$db->pdo->exec("INSERT INTO ttc_spielplan (id,mannschaft,datum) VALUES (1,1,'2099-01-01'),(2,1,'2000-01-01'),(3,0,'2099-01-01')");
        check((new SpielplaeneModel())->deleteSpielplan() === 1 && countRows() === 2);
    });
    test('Model constructor forwards MVC factory', function() {
        $factory = new class implements \Joomla\CMS\MVC\Factory\MVCFactoryInterface {};
        check((new SpielplaeneModel([], $factory))->factory === $factory);
    });
    test('Filters use component context and distinct cache keys', function() {
        $model = new class extends SpielplaeneModel {
            public function populate() { $this->populateState(); }
            public function key() { return $this->getStoreId(); }
        };
        Factory::$app->requests = ['filter_mannschaft'=>2, 'filter_offenespiele'=>1];
        $model->populate();
        check($model->getState('filter.mannschaft') === 2 && $model->getState('filter.offenespiele') === 1);
        $key = $model->key();
        $model->setState('filter.mannschaft', 3);
        check($key !== $model->key());
    });
    test('List query rejects arbitrary order expressions', function() {
        $model = new class extends SpielplaeneModel { public function query() { return $this->getListQuery(); } };
        $model->setState('list.ordering', 'id; DROP TABLE users');
        $model->setState('list.direction', 'DESC;DROP TABLE users');
        check(str_contains((string) $model->query(), 'ORDER BY a.id ASC'));
    });
    test('Duplicate uses save pipeline and rolls back vetoes', function() {
        (new SpielplaeneModel())->importSpielplan(csv());
        $model = new SpielplanModel();
        $ids = [1];
        check($model->duplicate($ids) && countRows() === 2);
        $model->failSave = true;
        rejects(function() use ($model, &$ids) { $model->duplicate($ids); });
        check(countRows() === 2);
    });
    test('Duplicate rejects denied or invalid selection', function() {
        $model = new SpielplanModel();
        foreach ([[], [0], [-1], ['1 OR 1=1']] as $ids) {
            rejects(function() use ($model, &$ids) { $model->duplicate($ids); });
        }
        Factory::$user->denied = ['core.create'];
        $ids = [1];
        rejects(function() use ($model, &$ids) { $model->duplicate($ids); });
    });
    test('Missing form returns false without dereferencing it', function() { check((new SpielplanModel())->getForm() === false); });
    test('Write controller actions all reject invalid CSRF', function() {
        Factory::$app->token = false;
        $controller = new \Ttc\Component\Spielplanung\Administrator\Controller\SpielplaeneController();
        foreach (['duplicate', 'importSpielplan', 'deleteSpielplan', 'saveOrderAjax'] as $method) {
            rejects(function() use ($controller, $method) { $controller->$method(); });
        }
        check(Factory::$db->writes === 0);
    });
    echo "All $tests regression tests passed.\n";
}
