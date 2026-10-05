<?php
namespace Ttc\Component\Spielplanung\Site\View\Saisonplanung;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    public $games;

    public $confirmedPlayers;

    public $onlyFuture;

    public $vorrundeOnly;

    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->id == 0) {
            // Logout may return to this protected view. Redirect without rendering an error page.
            $return = base64_encode('index.php?option=com_ttc_spielplanung&view=saisonplanung');
            Factory::getApplication()->redirect(Route::_('index.php?option=com_users&view=login&return=' . rawurlencode($return), false));
            return;
        }

        $input = Factory::getApplication()->input;
        if ($input->getBool('only_future_submitted', false)) {
            $this->onlyFuture = $input->getBool('only_future', false);
        } else {
            $this->onlyFuture = true;
        }
        $this->vorrundeOnly = $input->getBool('vorrunde', false);

        $model = $this->getModel();
        $this->games             = $model->getGames($this->onlyFuture, $this->vorrundeOnly);
        $this->confirmedPlayers  = $model->getConfirmedPlayers(array_keys($this->games));

        parent::display($tpl);
    }
}
