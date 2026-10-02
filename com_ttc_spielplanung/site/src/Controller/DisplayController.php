<?php
namespace Ttc\Component\Spielplanung\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class DisplayController extends BaseController
{
    protected $default_view = 'spielplan';

    public function saveGame()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        if ($user->id == 0) {
            $return = base64_encode('index.php?option=com_ttc_spielplanung&view=spielplan');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login&return=' . rawurlencode($return), false));
            return;
        }

        $gameIds = $app->input->post->get('game_ids', array(), 'array');

        $statuses = array();
        foreach ($gameIds as $gameId) {
            if ((!is_int($gameId) && !is_string($gameId))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $gameId)
                || filter_var($gameId, FILTER_VALIDATE_INT) === false) {
                $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_ERROR'), 'error');
                $this->setRedirect('index.php?option=com_ttc_spielplanung&view=spielplan');
                return;
            }

            // Keep the original value: coercion would turn malformed input into a valid status.
            $statuses[(int) $gameId] = $app->input->post->get('status_' . $gameId, null, 'raw');
        }

        $model = $this->getModel('Spielplan');
        $result = $model->saveAndNotify($statuses);

        if ($result !== false) {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_SUCCESS'));

            foreach ($result['warnings'] as $warning) {
                $app->enqueueMessage($warning, 'warning');
            }
        } else {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_ERROR'), 'error');
        }

        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=spielplan');
    }
}
