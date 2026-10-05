<?php
namespace Ttc\Component\Spielplanung\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class SaisonplanungmfController extends BaseController
{
    protected $default_view = 'saisonplanungmf';

    public function saveGame()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        if ($user->id == 0) {
            $return = base64_encode('index.php?option=com_ttc_spielplanung&view=saisonplanungmf');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login&return=' . rawurlencode($return), false));
            return;
        }

        $playerId = $app->input->post->getInt('player_id');
        $returnUrl = 'index.php?option=com_ttc_spielplanung&view=saisonplanungmf'
            . '&player_id=' . $playerId
            . '&only_future_submitted=1&only_future=' . (int) $app->input->post->getBool('only_future')
            . '&vorrunde=' . (int) $app->input->post->getBool('vorrunde')
            . '&Itemid=' . $app->input->post->getInt('Itemid');

        $gameIds = $app->input->post->get('game_ids', array(), 'array');

        $statuses = array();
        foreach ($gameIds as $gameId) {
            if ((!is_int($gameId) && !is_string($gameId))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $gameId)
                || filter_var($gameId, FILTER_VALIDATE_INT) === false) {
                $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_ERROR'), 'error');
                $this->setRedirect(Route::_($returnUrl, false));
                return;
            }

            // Keep the original value: coercion would turn malformed input into a valid status.
            $value = $app->input->post->get('status_' . $gameId, '', 'raw');
            $statuses[(int) $gameId] = $value === 'neutral' ? null : $value;
        }

        $model = $this->getModel('Saisonplanungmf');
        $model->setPlayerId($playerId);
        $result = $model->saveAndNotify($statuses);

        if ($result !== false) {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_SUCCESS'));

            foreach ($result['warnings'] as $warning) {
                $app->enqueueMessage($warning, 'warning');
            }
        } else {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_ERROR'), 'error');
        }

        $this->setRedirect(Route::_($returnUrl, false));
    }
}
