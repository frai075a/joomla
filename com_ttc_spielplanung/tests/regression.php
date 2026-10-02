<?php
/**
 * Standalone regression tests: php -d extension=pdo_sqlite tests/regression.php
 * Joomla services are doubled; model queries execute against an in-memory SQLite database.
 */
namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {
        public $state = array();
        public function getState($key) { return $this->state[$key] ?? null; }
        public function getDatabase() { return \Joomla\CMS\Factory::$db; }
    }
    class ListModel extends BaseDatabaseModel {
        public function __construct($config = array()) {}
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _($url, $xhtml = true) { return $url; }
    }
}
namespace Joomla\CMS\MVC\View {
    class HtmlView {
        public $model;
        public $rendered = false;
        public function getModel() {
            if (!$this->model) { throw new \RuntimeException('Guest must not load model data'); }
            return $this->model;
        }
        public function display($tpl = null) { $this->rendered = true; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        public function checkToken() {
            if (!\Joomla\CMS\Factory::$app->tokenValid) {
                throw new \RuntimeException('Invalid token');
            }
        }
        public $redirectUrl;
        public function setRedirect($url) { $this->redirectUrl = $url; }
        public function setMessage($message) {}
        public function getModel($name) {
            if ($name === 'Saisonplanungmf') { return new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungmfModel(); }
            if ($name === 'Mmb') { return new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel(); }
            return $name === 'Spielplan'
                ? new \Ttc\Component\Spielplanung\Site\Model\SpielplanModel()
                : new \Ttc\Component\Spielplanung\Administrator\Model\KategorienModel();
        }
    }
    class AdminController extends BaseController {
        public $input;
        public function __construct() { $this->input = \Joomla\CMS\Factory::$app->input; }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _($text) { return $text; }
        public static function sprintf($key, ...$args) { return $key . ': ' . implode(' | ', $args); }
    }
}
namespace Joomla\CMS\HTML {
    class HTMLHelper {
        public static function _($helper, $value = null, $format = null) { return $value; }
    }
}
namespace Joomla\CMS {
    class Factory {
        public static $db;
        public static $user;
        public static $app;
        public static $sent = array();
        public static $mailFailure = null;
        public static function createTestMailer() {
            return new class {
                public $body;
                public $subject;
                public $recipient;
                public function setSubject($value) { $this->subject = $value; }
                public function setBody($value) { $this->body = $value; }
                public function addRecipient($value) { $this->recipient = $value; }
                public function Send() {
                    if (Factory::$mailFailure === 'throw') { throw new \RuntimeException('SMTP failed'); }
                    if (Factory::$mailFailure === 'false') { return false; }
                    Factory::$sent[] = $this;
                    return true;
                }
            };
        }
        public static function createTestSession() {
            return new class { public function getFormToken() { return 'test-token'; } };
        }

        public static function getApplication() { return self::$app; }
        public static function getDate() {
            return new class { public function toSql() { return '2026-09-29 12:00:00'; } };
        }
        public static function getContainer() {
            return new class {
                public function get($name) {
                    if ($name === \Joomla\Database\DatabaseInterface::class) { return Factory::$db; }
                    if ($name === \Joomla\CMS\Mail\MailerFactoryInterface::class) {
                        return new class {
                            public function createMailer() { return Factory::createTestMailer(); }
                        };
                    }
                    throw new \RuntimeException('Unexpected service: ' . $name);
                }
            };
        }
    }
}
namespace {
    defined('_JEXEC') || define('_JEXEC', 1);
    use Joomla\CMS\Factory;
    use Ttc\Component\Spielplanung\Site\Model\SpielplanModel;
    use Ttc\Component\Spielplanung\Administrator\Model\KategorienModel;

    require __DIR__ . '/../administrator/src/Repository/SpielplanungRepository.php';
    require __DIR__ . '/../site/src/Model/SaisonplanungModel.php';
    require __DIR__ . '/../administrator/src/Model/SaisonplanungModel.php';
    require __DIR__ . '/../administrator/src/Model/MmbModel.php';
    require __DIR__ . '/../site/src/Service/CaptainNotificationService.php';
    require __DIR__ . '/../administrator/src/Controller/MmbController.php';
    require __DIR__ . '/../site/src/Model/SpielplanModel.php';
    require __DIR__ . '/../site/src/Model/SaisonplanungmfModel.php';
    require __DIR__ . '/../site/src/Controller/SaisonplanungmfController.php';
    require __DIR__ . '/../site/src/View/Saisonplanungmf/HtmlView.php';
    require __DIR__ . '/../administrator/src/Model/KategorienModel.php';
    require __DIR__ . '/../site/src/View/Spielplan/HtmlView.php';
    require __DIR__ . '/../site/src/View/Saisonplanung/HtmlView.php';
    require __DIR__ . '/../site/src/Controller/DisplayController.php';
    require __DIR__ . '/../administrator/src/Controller/KategorienController.php';
    require __DIR__ . '/../script.php';

    class Query {
        public $type = 'select';
        public $table;
        public $selects = array();
        public $joins = array();
        public $where = array();
        public $sets = array();
        public $columns = array();
        public $values;
        public $order;
        public function select($s) { $this->selects[] = $s; return $this; }
        public function from($s) { $this->table = $s; return $this; }
        public function innerJoin($s) { $this->joins[] = ' INNER JOIN ' . $s; return $this; }
        public function leftJoin($s) { $this->joins[] = ' LEFT JOIN ' . $s; return $this; }
        public function where($s) { $this->where[] = $s; return $this; }
        public function order($s) { $this->order = $s; return $this; }
        public function insert($s) { $this->type = 'insert'; $this->table = $s; return $this; }
        public function update($s) { $this->type = 'update'; $this->table = $s; return $this; }
        public function delete($s) { $this->type = 'delete'; $this->table = $s; return $this; }
        public function columns($s) { $this->columns = $s; return $this; }
        public function values($s) { $this->values = $s; return $this; }
        public function set($s) { $this->sets[] = $s; return $this; }
        public function __toString() {
            switch ($this->type) {
                case 'insert': return 'INSERT INTO ' . $this->table . ' (' . implode(',', $this->columns) . ') VALUES (' . $this->values . ')';
                case 'update': $sql = 'UPDATE ' . $this->table . ' SET ' . implode(',', $this->sets); break;
                case 'delete': $sql = 'DELETE FROM ' . $this->table; break;
                default: $sql = 'SELECT ' . implode(',', $this->selects) . ' FROM ' . $this->table . implode('', $this->joins);
            }
            if ($this->where) { $sql .= ' WHERE ' . implode(' AND ', $this->where); }
            if ($this->order) { $sql .= ' ORDER BY ' . $this->order; }
            return $sql;
        }
    }
    class Database {
        public $pdo;
        public $query;
        public $writes = 0;
        public $failAt = 0;
        public function __construct() {
            $this->pdo = new \PDO('sqlite::memory:');
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        public function getQuery($new) { return new Query(); }
        public function quoteName($s) { return '"' . str_replace('#__', '', $s) . '"'; }
        public function quote($s) { return $this->pdo->quote($s); }
        public function setQuery($q) { $this->query = (string) $q; }
        public function loadColumn() { return $this->pdo->query($this->query)->fetchAll(\PDO::FETCH_COLUMN); }
        public function loadResult() { return $this->pdo->query($this->query)->fetchColumn(); }
        public function loadObject() { return $this->pdo->query($this->query)->fetchObject(); }
        public function loadAssocList($key = null) {
            $rows = $this->pdo->query($this->query)->fetchAll(\PDO::FETCH_ASSOC);
            return $key === null ? $rows : array_column($rows, null, $key);
        }
        public function execute() {
            if (++$this->writes === $this->failAt) { throw new \RuntimeException('Simulated write failure'); }
            $this->pdo->exec($this->query);
        }
        public function transactionStart() { $this->pdo->beginTransaction(); }
        public function transactionCommit() { $this->pdo->commit(); }
        public function transactionRollback() { $this->pdo->rollBack(); }
    }
    class Input {
        public $post;
        public $data = array();
        public function __construct() { $this->post = $this; }
        public function get($key, $default = null, $filter = null) { return $this->data[$key] ?? $default; }
        public function getInt($key) { return (int) $this->get($key); }
        public function getCmd($key) { return (string) $this->get($key); }
        public function getBool($key, $default = false) { return (bool) $this->get($key, $default); }
    }
    function fixture() {
        Factory::$sent = array();
        Factory::$mailFailure = null;
        Factory::$user = new class {
            public $id = 7;
            public $name = 'Player';
            public $allowed = true;
            public function authorise($action, $asset) { return $this->allowed; }
        };
        Factory::$app = new class {
            public $input;
            public $tokenValid = true;
            public $messages = array();
            public $redirects = array();
            public function getIdentity() { return Factory::$user; }
            public function getSession() { return Factory::createTestSession(); }
            public function redirect($url) { $this->redirects[] = $url; }
            public function __construct() { $this->input = new Input(); }
            public function enqueueMessage($message, $type = 'message') { $this->messages[] = $type; }
        };
        Factory::$db = new Database();
        Factory::$db->pdo->exec("
            CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, username TEXT, email TEXT);
            INSERT INTO users VALUES (7,'Player','player','player@example.test');
            CREATE TABLE categories (id INTEGER PRIMARY KEY, extension TEXT);
            INSERT INTO categories VALUES (10, 'com_content'), (20, 'com_content'), (30, 'com_content'), (40, 'other');
            CREATE TABLE ttc_relevante_kategorien (id INTEGER PRIMARY KEY, category_id INTEGER UNIQUE, sort_order INTEGER, state INTEGER, created TEXT, created_by INTEGER, modified TEXT, modified_by INTEGER);
            INSERT INTO ttc_relevante_kategorien VALUES (1,10,1,1,'original',1,NULL,0),(2,20,2,1,'original',1,NULL,0);
            CREATE TABLE ttc_mmb (user_id INTEGER UNIQUE, category_id INTEGER, state INTEGER, id INTEGER PRIMARY KEY, is_captain INTEGER DEFAULT 0, created TEXT, created_by INTEGER, modified TEXT, modified_by INTEGER);
            INSERT INTO ttc_mmb (user_id,category_id,state) VALUES (7,10,1);
            CREATE TABLE ttc_spielplan (id INTEGER, mannschaft INTEGER, datum TEXT, uhrzeit TEXT, ort TEXT, heimmannschaft TEXT, auswaertsmannschaft TEXT);
            INSERT INTO ttc_spielplan VALUES (101,1,'2026-10-01','19:00','Hall','TTC Nordend Frankfurt','Guest'),(102,2,'2026-10-02','19:00','Hall','Host','TTC Nordend Frankfurt');
            CREATE TABLE ttc_spielplanung (id INTEGER PRIMARY KEY, user_id INTEGER, game_id INTEGER, status INTEGER, state INTEGER, created TEXT, created_by INTEGER, modified TEXT, modified_by INTEGER, UNIQUE(user_id,game_id));
            CREATE TABLE ttc_hinrueckgrenze (datum TEXT);
            INSERT INTO ttc_hinrueckgrenze VALUES ('2026-01-01'),('2026-10-01');
        ");
        Factory::$db->pdo->exec("ALTER TABLE categories ADD COLUMN title TEXT; UPDATE categories SET title = 'Team ' || id");
        Factory::$db->pdo->exec("ALTER TABLE ttc_mmb ADD COLUMN position INTEGER; UPDATE ttc_mmb SET position = 2");
        Factory::$db->pdo->sqliteCreateFunction('CURDATE', function() { return '2026-09-29'; }, 0);
    }
    function check($condition, $message = 'Unexpected result') {
        if (!$condition) { throw new \RuntimeException($message); }
    }
    function rejects($callback) {
        try { $callback(); } catch (\Exception $e) { return; }
        throw new \RuntimeException('Expected rejection');
    }
    function countAvailability() { return (int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_spielplanung')->fetchColumn(); }
    function test($name, $callback) {
        fixture();
        $callback();
        echo "PASS: $name\n";
    }


    function mfFixture() {
        Factory::$db->pdo->exec("ALTER TABLE users ADD COLUMN block INTEGER DEFAULT 0");
        Factory::$db->pdo->exec("UPDATE ttc_mmb SET category_id=20,is_captain=1 WHERE user_id=7;
            INSERT INTO ttc_relevante_kategorien (category_id,sort_order,state) VALUES (30,3,1);
            INSERT INTO users (id,name,username,email) VALUES (8,'Same','same','same@example.test'),(9,'Third','third','third@example.test'),(10,'First','first','first@example.test');
            INSERT INTO ttc_mmb (user_id,category_id,state,position) VALUES (8,20,1,3),(9,30,1,1),(10,10,1,1);");
        $model = new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungmfModel();
        $model->setPlayerId(9);
        return $model;
    }
    test('MF offers only same and numerically higher teams', function() {
        $model = mfFixture();
        check(array_keys($model->getPlayers()) === array(7,8,9));
        check(array_keys($model->getGames(false)) === array(102));
        check(array_keys($model->getGames(true)) === array(102));
        $model->setPlayerId(8);
        check(array_keys($model->getGames(false)) === array(102));
    });
    test('MF stores selected player and records actual actor on insert and update', function() {
        $model = mfFixture();
        check($model->saveGames(array(102 => 0)) !== false);
        $row = Factory::$db->pdo->query('SELECT * FROM ttc_spielplanung')->fetchObject();
        check((int) $row->user_id === 9 && (int) $row->created_by === 7);
        check((int) $model->getGames(false)[102]['status'] === 0);
        check($model->saveGames(array(102 => 1)) !== false);
        $row = Factory::$db->pdo->query('SELECT * FROM ttc_spielplanung')->fetchObject();
        check((int) $row->user_id === 9 && (int) $row->modified_by === 7);
        check($model->getConfirmedPlayers(array(102)) === array(102 => array('Third')));
        $model->setPlayerId(8);
        check($model->getGames(false)[102]['status'] === null);
    });
    test('MF rejects other team games atomically', function() {
        $model = mfFixture();
        check($model->saveGames(array(102 => 0, 101 => 1)) === false);
        check(countAvailability() === 0 && Factory::$db->writes === 0);
    });
    test('MF rejects ineligible and missing player IDs', function() {
        $model = mfFixture();
        foreach (array(10, 999, 0, -1) as $id) {
            $model->setPlayerId($id);
            check($model->getGames(false) === array());
            check($model->saveGames(array(102 => 0)) === false);
        }
        check(Factory::$db->writes === 0);
    });
    test('MF noncaptains and guests cannot read or save', function() {
        $model = mfFixture();
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=0');
        check($model->getGames(false) === array());
        check($model->saveGames(array(102 => 1)) === false);
        Factory::$user->id = 0;
        check($model->getPlayers() === array());
        check($model->saveGames(array(102 => 1)) === false);
        check(Factory::$db->writes === 0);
    });
    test('MF revalidates blocked player, inactive team and membership before save', function() {
        $model = mfFixture();
        foreach (array('UPDATE users SET block=1 WHERE id=9', 'UPDATE ttc_mmb SET state=0 WHERE user_id=9', 'UPDATE ttc_relevante_kategorien SET state=0 WHERE category_id=30') as $sql) {
            Factory::$db->pdo->exec($sql);
            check($model->saveGames(array(102 => 0)) === false);
            Factory::$db->pdo->exec('UPDATE users SET block=0; UPDATE ttc_mmb SET state=1; UPDATE ttc_relevante_kategorien SET state=1');
        }
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET state=0 WHERE user_id=7');
        check($model->saveGames(array(102 => 0)) === false);
        check(Factory::$db->writes === 0);
    });
    test('MF invalid statuses and failed writes do not partially save', function() {
        $model = mfFixture();
        foreach (array(null, 'yes', 2, true, array(1)) as $status) {
            check($model->saveGames(array(102 => $status)) === false);
        }
        Factory::$db->failAt = 1;
        check($model->saveGames(array(102 => 0)) === false);
        check(countAvailability() === 0);
    });
    test('MF save never sends captain notification mail, even on what would be a mail failure', function() {
        $model = mfFixture();
        check($model->saveAndNotify(array(102 => 0)) === array('warnings' => array()));
        check(count(Factory::$sent) === 0);
        Factory::$mailFailure = 'throw';
        check($model->saveAndNotify(array(102 => 1)) === array('warnings' => array()));
        check((int) Factory::$db->pdo->query('SELECT status FROM ttc_spielplanung')->fetchColumn() === 1);
        check(count(Factory::$sent) === 0);
    });
    test('MF controller checks CSRF and preserves selection, filter and menu', function() {
        mfFixture();
        $controller = new \Ttc\Component\Spielplanung\Site\Controller\SaisonplanungmfController();
        Factory::$app->tokenValid = false;
        rejects(function() use ($controller) { $controller->saveGame(); });
        check(Factory::$db->writes === 0);
        Factory::$app->tokenValid = true;
        Factory::$app->input->data = array('player_id'=>9, 'game_ids'=>array(102), 'status_102'=>'0', 'only_future'=>0, 'Itemid'=>42);
        $controller->saveGame();
        check(countAvailability() === 1);
        check(str_contains($controller->redirectUrl, 'player_id=9&only_future_submitted=1&only_future=0&vorrunde=0&Itemid=42'));
    });
    test('MF view enforces captain access and defaults to own player', function() {
        $model = mfFixture();
        $view = new \Ttc\Component\Spielplanung\Site\View\Saisonplanungmf\HtmlView();
        $view->model = $model;
        $view->display();
        check($view->playerId === 7 && array_keys($view->games) === array(102));
        Factory::$app->input->data = array('player_id'=>10);
        rejects(function() use ($view) { $view->display(); });
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=0');
        rejects(function() use ($view) { $view->display(); });
        Factory::$user->id = 0;
        $view->display();
        check(count(Factory::$app->redirects) === 1);
    });

    test('CSRF rejection happens before any write', function() {
        Factory::$app->tokenValid = false;
        rejects(function() { (new \Ttc\Component\Spielplanung\Site\Controller\DisplayController())->saveGame(); });
        check(Factory::$db->writes === 0);
    });
    test('Own game accepts both valid status values', function() {
        $model = new SpielplanModel();
        check(count($model->saveGames(array(101 => '0'))) === 1);
        check(count($model->saveGames(array(101 => '1'))) === 1);
        check(countAvailability() === 1);
    });
    test('Foreign game rejects entire mixed batch', function() {
        check((new SpielplanModel())->saveGames(array(101 => 0, 102 => 1)) === false);
        check(countAvailability() === 0 && Factory::$db->writes === 0);
    });
    test('Unknown game rejects entire mixed batch', function() {
        check((new SpielplanModel())->saveGames(array(101 => 0, 999 => 1)) === false);
        check(Factory::$db->writes === 0);
    });
    test('Invalid statuses are never coerced to zero or one', function() {
        foreach (array(-1, 2, 'yes', '1x', '', null, true, array(1), 1.0) as $status) {
            check((new SpielplanModel())->saveGames(array(101 => $status)) === false);
        }
        check(Factory::$db->writes === 0);
    });
    test('Malformed IDs and empty requests do not write', function() {
        foreach (array(array(), array(0 => 1), array(-1 => 1), array('101x' => 1)) as $data) {
            check((new SpielplanModel())->saveGames($data) === false);
        }
        check(Factory::$db->writes === 0);
    });
    test('Guest and inactive membership cannot save', function() {
        Factory::$user->id = 0;
        check((new SpielplanModel())->saveGames(array(101 => 1)) === false);
        Factory::$user->id = 7;
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET state=0');
        check((new SpielplanModel())->saveGames(array(101 => 1)) === false);
        check(Factory::$db->writes === 0);
    });
    test('Inactive team cannot save', function() {
        Factory::$db->pdo->exec('UPDATE ttc_relevante_kategorien SET state=0');
        check((new SpielplanModel())->saveGames(array(101 => 1)) === false);
        check(Factory::$db->writes === 0);
    });
    test('Frontend rejects malformed IDs and missing statuses', function() {
        foreach (array(array('game_ids' => array('101x')), array('game_ids' => array(101))) as $data) {
            Factory::$app->input->data = $data;
            (new \Ttc\Component\Spielplanung\Site\Controller\DisplayController())->saveGame();
        }
        check(Factory::$db->writes === 0);
    });
    test('Saving one category preserves unseen categories and creation metadata', function() {
        (new KategorienModel())->saveSelection(array(10 => array('checked' => '1', 'sort_order' => '3')));
        $rows = Factory::$db->pdo->query('SELECT * FROM ttc_relevante_kategorien ORDER BY category_id')->fetchAll(\PDO::FETCH_ASSOC);
        check(count($rows) === 2 && (int) $rows[0]['sort_order'] === 3 && (int) $rows[1]['sort_order'] === 2);
        check($rows[0]['created'] === 'original');
    });
    test('Explicit deselection only deletes submitted category', function() {
        (new KategorienModel())->saveSelection(array(10 => array('checked' => '0')));
        check(Factory::$db->pdo->query('SELECT category_id FROM ttc_relevante_kategorien')->fetchAll(\PDO::FETCH_COLUMN) == array(20));
    });
    test('New category can be selected', function() {
        (new KategorienModel())->saveSelection(array(30 => array('checked' => '1', 'sort_order' => '3')));
        check((int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_relevante_kategorien')->fetchColumn() === 3);
    });
    test('Empty, incomplete, invalid and foreign-extension selections are rejected', function() {
        foreach (array(array(), array(10 => array()), array(10 => array('checked' => '1', 'sort_order' => '100')),
            array(999 => array('checked' => '0')), array(40 => array('checked' => '0'))) as $data) {
            rejects(function() use ($data) { (new KategorienModel())->saveSelection($data); });
        }
        check(Factory::$db->writes === 0);
    });
    test('Whole selection is validated before deleting any row', function() {
        rejects(function() {
            (new KategorienModel())->saveSelection(array(10 => array('checked' => '0'), 20 => array('checked' => '1', 'sort_order' => 'bad')));
        });
        check(Factory::$db->writes === 0);
    });
    test('Partial database failure rolls category changes back', function() {
        Factory::$db->failAt = 2;
        rejects(function() {
            (new KategorienModel())->saveSelection(array(10 => array('checked' => '0'), 20 => array('checked' => '0')));
        });
        check((int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_relevante_kategorien')->fetchColumn() === 2);
    });
    test('Truncated category form is rejected', function() {
        Factory::$app->input->data = array('kategorien' => array(10 => array('checked' => '0')));
        (new \Ttc\Component\Spielplanung\Administrator\Controller\KategorienController())->save();
        check(Factory::$db->writes === 0 && Factory::$app->messages === array('error'));
    });
    test('Category write permission is enforced by model', function() {
        Factory::$user->allowed = false;
        rejects(function() { (new KategorienModel())->saveSelection(array(10 => array('checked' => '0'))); });
        check(Factory::$db->writes === 0);
    });

    class SchemaDatabase {
        public $columns = array();
        public $writes = 0;
        public $fail = false;
        public function getTableColumns($table, $types) { return $this->columns; }
        public function quoteName($name) { return $name; }
        public function setQuery($sql) {}
        public function execute() {
            if ($this->fail) { throw new \RuntimeException('Schema write failed'); }
            $this->writes++;
            $this->columns['is_captain'] = true;
        }
    }
    test('Installer repairs missing captain column and is repeatable', function() {
        Factory::$db = new SchemaDatabase();
        $installer = new \com_ttc_spielplanungInstallerScript();
        $installer->postflight('update', null);
        $installer->postflight('update', null);
        check(Factory::$db->writes === 1);
    });
    test('Installer leaves existing captain column unchanged', function() {
        Factory::$db = new SchemaDatabase();
        Factory::$db->columns['is_captain'] = true;
        (new \com_ttc_spielplanungInstallerScript())->postflight('install', null);
        check(Factory::$db->writes === 0);
    });
    test('Installer does not hide schema repair failure', function() {
        Factory::$db = new SchemaDatabase();
        Factory::$db->fail = true;
        rejects(function() { (new \com_ttc_spielplanungInstallerScript())->postflight('update', null); });
    });

    test('Both frontend lists retain team scope and distinct return formats', function() {
        $games = (new SpielplanModel())->getGames(false);
        $season = (new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel())->getGames(false);
        check(array_keys($games) === array(0));
        check(array_keys($season) === array(101));
        check($games[0]['game_id'] === $season[101]['game_id']);
        check($games[0]['gegner'] === 'Guest' && $games[0]['username'] === 'player');
        check(array_key_exists('status', $games[0]) && $games[0]['status'] === null);
        check(!array_key_exists('status', $season[101]));
        Factory::$user->id = 0;
        check((new SpielplanModel())->getGames(false) === array());
        check((new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel())->getGames(false) === array());
    });
    test('Vorrunde filter uses the latest ttc_hinrueckgrenze date and is shared by frontend lists', function() {
        Factory::$db->pdo->exec("INSERT INTO ttc_spielplan VALUES (103,1,'2026-10-02','19:00','Hall','TTC Nordend Frankfurt','Second guest')");
        // Grenze liegt bei 2026-10-01 (MAX der beiden Fixture-Werte): game 101 (2026-10-01) zählt noch zur Vorrunde, game 102 (2026-10-02) nicht mehr.
        foreach (array(new SpielplanModel(), new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel()) as $model) {
            check(count($model->getGames(false, true)) === 1);
            check(count($model->getGames(false, false)) === 2);
        }
    });
    test('Future filter and inactive membership are shared by frontend lists', function() {
        Factory::$db->pdo->exec("UPDATE ttc_spielplan SET datum='2026-01-01' WHERE id=101");
        foreach (array(new SpielplanModel(), new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel()) as $model) {
            check($model->getGames(true) === array());
            check(count($model->getGames(false)) === 1);
        }
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET state=0');
        check((new SpielplanModel())->getGames(false) === array());
        check((new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel())->getGames(false) === array());
    });
    test('Backend games retain all-team scope, category filter and opponent labels', function() {
        $model = new class extends \Ttc\Component\Spielplanung\Administrator\Model\SaisonplanungModel {
            public function queryForTest() { return $this->getListQuery(); }
        };
        Factory::$db->setQuery($model->queryForTest());
        $rows = Factory::$db->loadAssocList('game_id');
        check(array_keys($rows) === array(101, 102));
        check($rows[101]['gegner'] === 'Guest' && $rows[102]['gegner'] === 'Host');
        check($rows[102]['category_title'] === 'Team 20');
        $model->state['filter.category_id'] = 20;
        Factory::$db->setQuery($model->queryForTest());
        check(array_keys(Factory::$db->loadAssocList('game_id')) === array(102));
    });
    test('Both season models share confirmed-player ordering and empty handling', function() {
        Factory::$db->pdo->exec("
            INSERT INTO users VALUES (8,'First','first','first@example.test'),(9,'Unassigned','unassigned','none@example.test'),(11,'Declined','declined','no@example.test');
            INSERT INTO ttc_mmb (user_id,category_id,state,position) VALUES (8,10,1,1),(11,10,1,3);
            INSERT INTO ttc_spielplanung (user_id,game_id,status) VALUES (7,101,1),(8,101,1),(9,101,1),(11,101,0),(8,102,1);
        ");
        foreach (array(new \Ttc\Component\Spielplanung\Site\Model\SaisonplanungModel(),
            new \Ttc\Component\Spielplanung\Administrator\Model\SaisonplanungModel()) as $model) {
            check($model->getConfirmedPlayers(array()) === array());
            check($model->getConfirmedPlayers(array(101)) === array(101 => array('First','Player','Unassigned')));
            check($model->getConfirmedPlayers(array(999)) === array());
        }
    });
    test('Both backend category lists share active filtering and alphabetical order', function() {
        Factory::$db->pdo->exec("UPDATE categories SET title='Alpha' WHERE id=20");
        $mmb = new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel();
        $season = new \Ttc\Component\Spielplanung\Administrator\Model\SaisonplanungModel();
        check($mmb->getRelevantCategories() === $season->getRelevantCategories());
        check(array_keys($mmb->getRelevantCategories()) === array(20,10));
        Factory::$db->pdo->exec('UPDATE ttc_relevante_kategorien SET state=0 WHERE category_id=20');
        check(array_keys($season->getRelevantCategories()) === array(10));
    });
    test('Shared user query never exposes matches for a missing user', function() {
        $repository = new \Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository(Factory::$db);
        Factory::$db->pdo->exec('INSERT INTO ttc_mmb (user_id,category_id,state,position) VALUES (0,10,1,1)');
        Factory::$db->setQuery($repository->createUserGamesQuery(0));
        check(Factory::$db->loadAssocList() === array());
    });


    test('Roster model saves rows and replaces the team captain', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        $model = new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel();
        check($model->saveRows(array(8 => array('category_id'=>10,'position'=>1,'is_captain'=>1))) === true);
        $captains = Factory::$db->pdo->query('SELECT user_id FROM ttc_mmb WHERE is_captain=1')->fetchAll(\PDO::FETCH_COLUMN);
        check($captains == array(8));
        $model->saveRows(array(8 => array('category_id'=>'','position'=>'','is_captain'=>1)));
        check((int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_mmb WHERE is_captain=1')->fetchColumn() === 0);
    });
    test('Roster model rolls back captain clearing when a write fails', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        Factory::$db->failAt = 2;
        rejects(function() {
            (new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel())->saveRows(array(8 => array('category_id'=>10,'position'=>1,'is_captain'=>1)));
        });
        check((int) Factory::$db->pdo->query('SELECT is_captain FROM ttc_mmb WHERE user_id=7')->fetchColumn() === 1);
        check((int) Factory::$db->pdo->query('SELECT COUNT(*) FROM ttc_mmb')->fetchColumn() === 1);
    });
    test('Roster writes reject direct model calls without permission', function() {
        Factory::$user->allowed = false;
        $model = new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel();
        rejects(function() use ($model) { $model->saveRows(array()); });
        rejects(function() use ($model) { $model->movePosition(7,10,'up'); });
        rejects(function() use ($model) { $model->updatePosition(7,10,1); });
        check(Factory::$db->writes === 0);
    });
    test('Model handles up/down movement and position bounds', function() {
        Factory::$db->pdo->exec('INSERT INTO ttc_mmb (user_id,category_id,state,position) VALUES (8,10,1,1)');
        $model = new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel();
        check($model->movePosition(7,10,'up') === true);
        check((int) Factory::$db->pdo->query('SELECT position FROM ttc_mmb WHERE user_id=7')->fetchColumn() === 1);
        check((int) Factory::$db->pdo->query('SELECT position FROM ttc_mmb WHERE user_id=8')->fetchColumn() === 2);
        check($model->movePosition(7,10,'up') === true);
        check((int) Factory::$db->pdo->query('SELECT position FROM ttc_mmb WHERE user_id=7')->fetchColumn() === 1);
        check($model->movePosition(7,10,'sideways') === false);
    });
    test('Roster controller delegates save and enforces CSRF', function() {
        Factory::$app->input->data = array('mmb'=>array(7=>array('category_id'=>10,'position'=>1)));
        (new \Ttc\Component\Spielplanung\Administrator\Controller\MmbController())->save();
        check((int) Factory::$db->pdo->query('SELECT position FROM ttc_mmb WHERE user_id=7')->fetchColumn() === 1);
        $writes = Factory::$db->writes;
        Factory::$app->tokenValid = false;
        rejects(function() { (new \Ttc\Component\Spielplanung\Administrator\Controller\MmbController())->save(); });
        check(Factory::$db->writes === $writes);
    });
    test('Save operation notifies only actual changes after commit', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        $model = new SpielplanModel();
        check($model->saveAndNotify(array(101=>0)) === array('warnings'=>array()));
        check(countAvailability() === 1 && count(Factory::$sent) === 1);
        check(strpos(Factory::$sent[0]->body, 'Guest') !== false);
        check($model->saveAndNotify(array(101=>0)) === array('warnings'=>array()));
        check(count(Factory::$sent) === 1);
        check($model->saveAndNotify(array(102=>0)) === false);
        check(count(Factory::$sent) === 1);
    });
    test('Mail exceptions and false return values do not undo availability saves', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        $model = new SpielplanModel();
        Factory::$mailFailure = 'throw';
        $result = $model->saveAndNotify(array(101=>0));
        check(count($result['warnings']) === 1 && countAvailability() === 1);
        Factory::$mailFailure = 'false';
        $result = $model->saveAndNotify(array(101=>1));
        check(count($result['warnings']) === 1);
        check((int) Factory::$db->pdo->query('SELECT status FROM ttc_spielplanung')->fetchColumn() === 1);
    });
    test('Captain lookup failures are warnings and do not undo saves', function() {
        $model = new class extends SpielplanModel {
            public function getCaptain($categoryId) { throw new \RuntimeException('Lookup failed'); }
        };
        check($model->saveAndNotify(array(101=>0)) === array('warnings'=>array('Lookup failed')));
        check(countAvailability() === 1);
    });
    test('Notification service groups changes and continues after a team fails', function() {
        $model = new class {
            public function getCaptain($categoryId) {
                if ($categoryId === 10) { throw new \RuntimeException('Lookup failed'); }
                if ($categoryId === 30) { return null; }
                return (object) array('email'=>'captain@example.test');
            }
        };
        $change = array('category_id'=>10,'spieldatum'=>'2026-10-01','uhrzeit'=>'19:00','gegner'=>'Guest','sporthalle'=>'Hall','old_status'=>1,'new_status'=>0);
        $second = $change; $second['category_id'] = 20;
        $third = $change; $third['category_id'] = 30;
        $warnings = (new \Ttc\Component\Spielplanung\Site\Service\CaptainNotificationService())->notify($model,Factory::$user,array($change,$second,$second,$third));
        check(count($warnings) === 1 && count(Factory::$sent) === 1);
        check(substr_count(Factory::$sent[0]->body,'Guest') === 2);
    });
    test('Frontend controller reports successful save and mail warning separately', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        Factory::$mailFailure = 'throw';
        Factory::$app->input->data = array('game_ids'=>array(101),'status_101'=>'0');
        (new \Ttc\Component\Spielplanung\Site\Controller\DisplayController())->saveGame();
        check(countAvailability() === 1);
        check(Factory::$app->messages === array('message','warning'));
    });


    foreach (array('Spielplan', 'Saisonplanung') as $viewName) {
        test('Guest/logout redirects safely from ' . $viewName, function() use ($viewName) {
            Factory::$user->id = 0;
            Factory::$app->messages = array('message'); // Existing logout-success message.
            $class = 'Ttc\\Component\\Spielplanung\\Site\\View\\' . $viewName . '\\HtmlView';
            $view = new $class();
            $view->display();
            check(!$view->rendered && Factory::$db->writes === 0);
            check(Factory::$app->messages === array('message'));
            check(count(Factory::$app->redirects) === 1);
            parse_str(parse_url(Factory::$app->redirects[0], PHP_URL_QUERY), $query);
            check($query['option'] === 'com_users' && $query['view'] === 'login');
            check(base64_decode($query['return']) === 'index.php?option=com_ttc_spielplanung&view=' . strtolower($viewName));
        });
    }
    test('Authenticated frontend views still load and render normally', function() {
        foreach (array('Spielplan','Saisonplanung') as $viewName) {
            $class = 'Ttc\\Component\\Spielplanung\\Site\\View\\' . $viewName . '\\HtmlView';
            $modelClass = 'Ttc\\Component\\Spielplanung\\Site\\Model\\' . $viewName . 'Model';
            $view = new $class();
            $view->model = new $modelClass();
            $view->display();
            check($view->rendered && count($view->games) === 1);
        }
        check(Factory::$app->redirects === array());
    });
    test('Guest save redirects to login without adding an error message', function() {
        Factory::$user->id = 0;
        $controller = new \Ttc\Component\Spielplanung\Site\Controller\DisplayController();
        $controller->saveGame();
        check(strpos($controller->redirectUrl, 'option=com_users&view=login') !== false);
        check(Factory::$app->messages === array() && Factory::$db->writes === 0);
    });


    test('Mixed assigned and unassigned roster rows can be saved', function() {
        $model = new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel();
        check($model->saveRows(array(
            7 => array('category_id'=>10,'position'=>2),
            8 => array('category_id'=>'')
        )) === true);
        $rows = Factory::$db->pdo->query('SELECT user_id,category_id,position,is_captain FROM ttc_mmb ORDER BY user_id')->fetchAll(\PDO::FETCH_ASSOC);
        check(count($rows) === 2 && (int) $rows[0]['category_id'] === 10 && (int) $rows[0]['position'] === 2);
        check($rows[1]['category_id'] === null && $rows[1]['position'] === null && (int) $rows[1]['is_captain'] === 0);
    });
    test('Removing assignment clears stale position and captain flag server-side', function() {
        Factory::$db->pdo->exec('UPDATE ttc_mmb SET is_captain=1');
        (new \Ttc\Component\Spielplanung\Administrator\Model\MmbModel())->saveRows(array(
            7 => array('category_id'=>'','position'=>5,'is_captain'=>1)
        ));
        $row = Factory::$db->pdo->query('SELECT category_id,position,is_captain FROM ttc_mmb WHERE user_id=7')->fetch(\PDO::FETCH_ASSOC);
        check($row['category_id'] === null && $row['position'] === null && (int) $row['is_captain'] === 0);
    });
    test('Roster template renders missing positions empty and unassigned controls disabled', function() {
        $view = new class {
            public $items;
            public $categories = array(10=>array('title'=>'Team'));
            public $pagination;
            public function getDocument() {
                return new class {
                    public function getWebAssetManager() {
                        return new class { public function useScript($name) {} };
                    }
                };
            }
            public function render() {
                ob_start();
                require __DIR__ . '/../administrator/tmpl/mmb/default.php';
                return ob_get_clean();
            }
        };
        $view->pagination = new class { public function getListFooter() { return ''; } };
        $view->items = array(
            (object) array('id'=>7,'name'=>'Assigned','username'=>'assigned','email'=>'','category_id'=>10,'position'=>2,'is_captain'=>0),
            (object) array('id'=>8,'name'=>'Unassigned','username'=>'unassigned','email'=>'','category_id'=>null,'position'=>null,'is_captain'=>0),
            (object) array('id'=>9,'name'=>'No position','username'=>'no-position','email'=>'','category_id'=>10,'position'=>null,'is_captain'=>0)
        );
        $html = $view->render();
        foreach (array(7,8,9) as $id) {
            check(preg_match('/<input type="number" name="mmb\[' . $id . '\]\[position\]"[^>]*>/', $html, $match) === 1);
            check(strpos($match[0], $id === 7 ? 'value="2"' : 'value=""') !== false);
            check((strpos($match[0], 'disabled="disabled"') !== false) === ($id === 8));
            check(strpos($match[0], 'required') === false);
        }
    });

    echo "All 54 regression tests passed.\n";
}
