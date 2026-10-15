<?php

/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenfussball\Component\Ttc_hallenfussball\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class Ttc_hallenfussballRouter
 *
 */
class Router extends RouterView
{
	private $noIDs;
	/**
	 * The category factory
	 *
	 * @var    CategoryFactoryInterface
	 *
	 * @since  2.1
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var    array
	 *
	 * @since  2.1
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = ComponentHelper::getParams('com_ttc_hallenfussball');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		
			$hallenfussballteilnehmer = new RouterViewConfiguration('hallenfussballteilnehmer');
			$this->registerView($hallenfussballteilnehmer);
			$ccHallenfussballteilnahme = new RouterViewConfiguration('hallenfussballteilnahme');
			$ccHallenfussballteilnahme->setKey('id')->setParent($hallenfussballteilnehmer);
			$this->registerView($ccHallenfussballteilnahme);
			$hallenfussballteilnahmeform = new RouterViewConfiguration('hallenfussballteilnahmeform');
			$hallenfussballteilnahmeform->setKey('id');
			$this->registerView($hallenfussballteilnahmeform);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}


	
		/**
		 * Method to get the segment(s) for an hallenfussballteilnahme
		 *
		 * @param   string  $id     ID of the hallenfussballteilnahme to retrieve the segments for
		 * @param   array   $query  The request that is built right now
		 *
		 * @return  array|string  The segments of this item
		 */
		public function getHallenfussballteilnahmeSegment($id, $query)
		{
			return array((int) $id => $id);
		}
			/**
			 * Method to get the segment(s) for an hallenfussballteilnahmeform
			 *
			 * @param   string  $id     ID of the hallenfussballteilnahmeform to retrieve the segments for
			 * @param   array   $query  The request that is built right now
			 *
			 * @return  array|string  The segments of this item
			 */
			public function getHallenfussballteilnahmeformSegment($id, $query)
			{
				return $this->getHallenfussballteilnahmeSegment($id, $query);
			}

	
		/**
		 * Method to get the segment(s) for an hallenfussballteilnahme
		 *
		 * @param   string  $segment  Segment of the hallenfussballteilnahme to retrieve the ID for
		 * @param   array   $query    The request that is parsed right now
		 *
		 * @return  mixed   The id of this item or false
		 */
		public function getHallenfussballteilnahmeId($segment, $query)
		{
			return (int) $segment;
		}
			/**
			 * Method to get the segment(s) for an hallenfussballteilnahmeform
			 *
			 * @param   string  $segment  Segment of the hallenfussballteilnahmeform to retrieve the ID for
			 * @param   array   $query    The request that is parsed right now
			 *
			 * @return  mixed   The id of this item or false
			 */
			public function getHallenfussballteilnahmeformId($segment, $query)
			{
				return $this->getHallenfussballteilnahmeId($segment, $query);
			}

	/**
	 * Method to get categories from cache
	 *
	 * @param   array  $options   The options for retrieving categories
	 *
	 * @return  CategoryInterface  The object containing categories
	 *
	 * @since   2.1
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key]))
		{
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
