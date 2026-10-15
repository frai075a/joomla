<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Event\Model\NormaliseRequestDataEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class TttrainingformController extends BaseController
{
    private function listRedirect(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_ttc_hallenbuchung&view=tttrainings', false));
    }

    public function save()
    {
        $this->checkToken();
        $model = $this->getModel('Tttrainingform', 'Site');
        $data = $this->input->post->get('jform', [], 'array');
        // Authorize before loading plugins or validating data.
        $model->getItem((int) ($data['id'] ?? 0));
        $form = $model->getForm([], false);
        if (!$form) { throw new \RuntimeException($model->getError()); }
        $event = new NormaliseRequestDataEvent('onContentNormaliseRequestData', [
            'context' => 'com_ttc_hallenbuchung.tttraining', 'data' => (object) $data, 'subject' => $form,
        ]);
        $this->app->getDispatcher()->dispatch($event->getName(), $event);
        $data = (array) $event->getData();
        $valid = $model->validate($form, $data);
        if ($valid === false || !$model->save($valid)) {
            $this->app->setUserState('com_ttc_hallenbuchung.edit.tttraining.data', $data);
            foreach ($model->getErrors() as $error) {
                $this->app->enqueueMessage($error instanceof \Throwable ? $error->getMessage() : (string) $error, 'warning');
            }
            $this->setRedirect(Route::_('index.php?option=com_ttc_hallenbuchung&view=tttrainingform&id=' . (int) ($data['id'] ?? 0), false));
            return false;
        }
        $this->app->setUserState('com_ttc_hallenbuchung.edit.tttraining.data', null);
        $this->setMessage(Text::_('COM_TTC_HALLENBUCHUNG_TT_SAVED'));
        $this->listRedirect();
        return true;
    }

    public function cancel()
    {
        $this->checkToken();
        $this->app->setUserState('com_ttc_hallenbuchung.edit.tttraining.data', null);
        $this->listRedirect();
    }

    public function remove()
    {
        $this->checkToken();
        $model = $this->getModel('Tttrainingform', 'Site');
        if (!$model->delete($this->input->post->getInt('id'))) { throw new \RuntimeException($model->getError()); }
        $this->setMessage(Text::_('COM_TTC_HALLENBUCHUNG_TT_DELETED'));
        $this->listRedirect();
    }
}
