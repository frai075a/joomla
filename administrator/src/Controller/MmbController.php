<?php
namespace Ttc\Component\Spielplanung\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class MmbController extends AdminController
{
    public function save()
    {
        $this->checkToken();
        $app = Factory::getApplication();

        try
        {
            $this->assertCanManage();
            $data = $this->input->post->get('mmb', array(), 'array');
            $this->getModel('Mmb')->saveRows($data);
            $this->setMessage(Text::_('COM_TTC_SPIELPLANUNG_SAVE_SUCCESS'));
        }
        catch (\Exception $e)
        {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=mmb');
    }

    public function movePosition()
    {
        $this->checkToken();
        $app = Factory::getApplication();

        try
        {
            $this->assertCanManage();
            $saved = $this->getModel('Mmb')->movePosition(
                $this->input->post->getInt('user_id'),
                $this->input->post->getInt('category_id'),
                $this->input->post->getCmd('direction')
            );
            $app->enqueueMessage(
                Text::_($saved ? 'COM_TTC_SPIELPLANUNG_SAVE_SUCCESS' : 'COM_TTC_SPIELPLANUNG_SAVE_ERROR'),
                $saved ? 'message' : 'error'
            );
        }
        catch (\Exception $e)
        {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=mmb');
    }

    private function assertCanManage()
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ttc_spielplanung'))
        {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_ACTION'));
        }
    }
}
