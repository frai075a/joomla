<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\CalendarField;
use Joomla\Registry\Registry;

/** Display German dates and store date-only SQL values without timezone shifts. */
class TeilnahmedatumField extends CalendarField
{
    protected $type = 'Teilnahmedatum';

    public function filter($value, $group = null, ?Registry $input = null)
    {
        $value = trim((string) $value);
        foreach (['d.m.Y', 'Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        // Required-field validation rejects empty and invalid dates.
        return '';
    }
}
