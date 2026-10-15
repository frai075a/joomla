<?php
namespace Ttc\Component\Spielplanung\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class EintraegeController extends BaseController
{
    public function remove()
    {
        $this->deleteEntries(false);
    }

    public function removeAll()
    {
        $this->deleteEntries(true);
    }

    private function deleteEntries($all)
    {
        $this->checkToken('post');
        $app = Factory::getApplication();
        try {
            $model = $this->getModel('Eintraege', 'Administrator');
            if ($all) {
                $model->removeAllEntries();
            } else {
                $model->removeEntry($app->input->post->get('entry_id', null, 'raw'));
            }
            $app->enqueueMessage(Text::_($all
                ? 'COM_TTC_SPIELPLANUNG_DELETE_ALL_SUCCESS'
                : 'COM_TTC_SPIELPLANUNG_DELETE_SUCCESS'));
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_DELETE_FAILED'), 'error');
        }
        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=eintraege');
    }
}
