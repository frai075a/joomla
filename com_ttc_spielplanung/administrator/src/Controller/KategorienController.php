<?php
namespace Ttc\Component\Spielplanung\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class KategorienController extends AdminController
{
    public function save()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $user = Factory::getApplication()->getIdentity();

        try
        {
            if (!$user->authorise('core.manage', 'com_ttc_spielplanung'))
            {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_ACTION'));
            }

            $data = $this->input->post->get('kategorien', array(), 'array');

            // This marker is after all rows, so a truncated form cannot delete selections.
            if (!$this->input->post->getBool('kategorien_complete', false) || empty($data))
            {
                throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_INVALID_CATEGORIES'));
            }

            $this->getModel('Kategorien')->saveSelection($data);
            $this->setMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_SUCCESS'));
        }
        catch (\Exception $e)
        {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=kategorien');
    }
}
