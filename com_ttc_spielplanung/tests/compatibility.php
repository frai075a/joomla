<?php
/**
 * Service wiring checks with Joomla API doubles, without compatibility methods.
 * Run: php tests/compatibility.php
 * These checks do not replace installation testing in a real Joomla 6 site.
 */
namespace Joomla\DI {
    interface ServiceProviderInterface { public function register(Container $container); }
    class Container {
        private $services = [];
        public function registerServiceProvider($provider) { $provider->register($this); }
        public function set($name, $service) { $this->services[$name] = $service; }
        public function get($name) {
            $service = $this->services[$name] ?? throw new \RuntimeException('Missing service: ' . $name);
            return $service instanceof \Closure ? $service($this) : $service;
        }
    }
}
namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Joomla\CMS\Dispatcher {
    interface ComponentDispatcherFactoryInterface {}
}
namespace Joomla\CMS\Extension {
    interface ComponentInterface {}
    class MVCComponent implements ComponentInterface {
        public $dispatcherFactory;
        private $mvcFactory;
        public function __construct($factory) { $this->dispatcherFactory = $factory; }
        public function setMVCFactory($factory) { $this->mvcFactory = $factory; }
        public function getMVCFactory() { return $this->mvcFactory; }
    }
}
namespace Joomla\CMS\Extension\Service\Provider {
    class MVCFactory {
        public function __construct(private $namespace) {}
        public function register($container) {
            if ($this->namespace !== '\Ttc\Component\Spielplanung') { throw new \RuntimeException('Wrong namespace'); }
            $container->set(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class,
                new class implements \Joomla\CMS\MVC\Factory\MVCFactoryInterface {});
        }
    }
    class ComponentDispatcherFactory {
        public function __construct($namespace) {}
        public function register($container) {
            $container->set(\Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface::class,
                new class implements \Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface {});
        }
    }
}
namespace Joomla\CMS\MVC\Model {
    class ListModel {
        public $injectedFactory;
        public function __construct($config = [], $factory = null) { $this->injectedFactory = $factory; }
    }
}
namespace Joomla\CMS\MVC\View {
    class HtmlView {
        public $model;
        public $rendered = false;
        public function getModel() { return $this->model; }
        public function display($tpl = null) { $this->rendered = true; }
        // Intentionally no legacy get() proxy.
    }
}
namespace {
    define('_JEXEC', 1);
    function check($value, $message) { if (!$value) { throw new \RuntimeException($message); } }
    foreach (['administrator', 'site'] as $side) {
        require __DIR__ . '/../' . $side . '/src/Extension/SpielplanungComponent.php';
        $provider = require __DIR__ . '/../' . $side . '/services/provider.php';
        $container = new \Joomla\DI\Container();
        $provider->register($container);
        $component = $container->get(\Joomla\CMS\Extension\ComponentInterface::class);
        check($component instanceof \Joomla\CMS\Extension\MVCComponent, 'Missing MVC component');
        check($component->getMVCFactory() === $container->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class), 'Missing MVC factory');
        check($component->dispatcherFactory === $container->get(\Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface::class), 'Missing dispatcher factory');
        echo "PASS: $side component service wiring\n";
    }
    $factory = $component->getMVCFactory();
    foreach (['Kategorien', 'Mmb', 'Saisonplanung'] as $name) {
        require __DIR__ . '/../administrator/src/Model/' . $name . 'Model.php';
        $class = '\Ttc\Component\Spielplanung\Administrator\Model\\' . $name . 'Model';
        check((new $class([], $factory))->injectedFactory === $factory, 'Model lost MVC factory: ' . $name);
        require __DIR__ . '/../administrator/src/View/' . $name . '/HtmlView.php';
        $class = '\Ttc\Component\Spielplanung\Administrator\View\\' . $name . '\HtmlView';
        $view = new $class();
        $view->model = new class {
            public $calls = [];
            public function getItems() { $this->calls[] = 'items'; return [(object) ['game_id' => 101]]; }
            public function getState() { $this->calls[] = 'state'; return new \stdClass(); }
            public function getPagination() { $this->calls[] = 'pagination'; return new \stdClass(); }
            public function getRelevantCategories() { return []; }
            public function getSelectedIds() { return []; }
            public function getConfirmedPlayers($ids) { check($ids === [101], 'Wrong game IDs'); return []; }
        };
        $view->display();
        check($view->rendered && $view->model->calls === ['items', 'state', 'pagination'], 'View failed: ' . $name);
        echo "PASS: $name model injection and view without legacy proxy\n";
    }
}