<?php
namespace Ttc\Component\Spielplanung\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class RelevanteKategorieTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__ttc_relevante_kategorien', 'id', $db);
    }
}
