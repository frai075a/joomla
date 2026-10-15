<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Model\TttrainingsModel as TrainingList;

class TttrainingsModel extends TrainingList
{
    protected function getListQuery()
    {
        $query = parent::getListQuery();
        // Calendar dates follow the site timezone, including today's midnight.
        $timezone = new \DateTimeZone(Factory::getApplication()->get('offset', 'UTC'));
        $today = Factory::getDate('now', $timezone)->format('Y-m-d', true);
        $query->where($this->getDatabase()->quoteName('a.datum') . ' >= ' . $this->getDatabase()->quote($today));
        if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_ttc_hallenbuchung')) {
            $query->where('a.state = 1');
        }
        return $query;
    }
}
