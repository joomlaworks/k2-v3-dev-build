<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';

class K2ModelItems extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('item').'.*')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->select($db->quoteName('category.title', 'categoryName'));
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Join over the language
		$query->select($db->quoteName('lang.title', 'languageTitle'));
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('item.language'));

		// Join over the asset groups.
		$query->select($db->quoteName('assetGroup.title', 'viewLevel'));
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('item.access'));

		// Join over the author
		$query->select($db->quoteName('user.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('item.created_by'));

		// Join over the moderator
		$query->select($db->quoteName('user.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('item.modified_by'));

		// Join over the hits
		$query->select($db->quoteName('stats.hits', 'hits'));
		$query->leftJoin($db->quoteName('#__k2_stats', 'stats').' ON '.$db->quoteName('stats.itemId').' = '.$db->quoteName('item.id'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.items.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data);

		// Return rows
		return (array)$rows;
	}

	public function countRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.items.count');

		// Set the query
		$db->setQuery($query);

		// Get the result
		$total = $db->loadResult();

		// Return the result
		return (int)$total;
	}

	private function setQueryConditions(&$query)
	{
		$db = $this->getDBO();
		if ($this->getState('language'))
		{
			$query->where($db->quoteName('item.language').' = '.$db->quote($this->getState('language')));
		}
		if (is_numeric($this->getState('published')))
		{
			$query->where($db->quoteName('item.published').' = '.(int)$this->getState('published'));
		}
		if (is_numeric($this->getState('featured')))
		{
			$query->where($db->quoteName('item.featured').' = '.(int)$this->getState('featured'));
		}
		if (is_numeric($this->getState('trashed')))
		{
			$query->where($db->quoteName('item.trashed').' = '.(int)$this->getState('trashed'));
		}
		if ($this->getState('category'))
		{
			$query->where($db->quoteName('item.catid').' = '.(int)$this->getState('category'));
		}
		if ($this->getState('access'))
		{
			$query->where($db->quoteName('item.access').' = '.(int)$this->getState('access'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('item.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('item.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('item.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('author'))
		{
			$query->where($db->quoteName('item.created_by').' = '.(int)$this->getState('author'));
		}
		if ($this->getState('publish_up'))
		{
			$query->where('('.$db->quoteName('item.publish_up').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_up').' <= '.$db->Quote($this->getState('publish_up')).')');
		}
		if ($this->getState('publish_down'))
		{
			$query->where('('.$db->quoteName('item.publish_down').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_down').' >= '.$db->Quote($this->getState('publish_down')).')');
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('item.title').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('item.id').' = '.(int)$search.'
				OR LOWER('.$db->quoteName('item.introtext').') LIKE '.$db->Quote('%'.$search.'%', false).'
				OR LOWER('.$db->quoteName('item.fulltext').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		$order = null;
		if ($sorting)
		{
			switch($sorting)
			{
				case 'id' :
					$order = 'item.id DESC';
					break;
				case 'title' :
					$order = 'item.title ASC';
					break;
				case 'ordering' :
					$order = 'item.ordering ASC';
					break;
				case 'featured' :
					$order = 'item.featured DESC';
					break;
				case 'published' :
					$order = 'item.published DESC';
					break;
				case 'category' :
					$order = 'categoryName ASC';
					break;
				case 'author' :
					$order = 'authorName ASC';
					break;
				case 'moderator' :
					$order = 'moderatorName ASC';
					break;
				case 'access' :
					$order = 'viewLevel ASC';
					break;
				case 'created' :
					$order = 'item.created DESC';
					break;
				case 'modified' :
					$order = 'item.modified DESC';
					break;
				case 'hits' :
					$order = 'hits DESC';
					break;
				case 'language' :
					$order = 'languageTitle ASC';
					break;
			}
		}
		// Append sorting
		if ($order)
		{
			$query->order($order);
		}
	}

	/**
	 * onBeforeSave method.
	 * @param   array  $data     The data to be saved.
	 *
	 * @return void
	 */

	protected function onBeforeSave(&$data)
	{
		$user = JFactory::getUser();
		$configuration = JFactory::getConfig();
		$userTimeZone = $user->getParam('timezone', $configuration->get('offset'));

		// Handle date data
		if ($data['id'] && isset($data['createdDate']))
		{
			// Convert date to UTC
			$createdDateTime = $data['createdDate'].' '.$data['createdTime'];
			$data['created'] = JFactory::getDate($createdDateTime, $userTimeZone)->toSql();
		}

		if (isset($data['publishUpDate']) && isset($data['publishUpTime']))
		{
			// Convert date to UTC
			$publishUpDateTime = $data['publishUpDate'].' '.$data['publishUpTime'];
			if ((int)$publishUpDateTime > 0)
			{
				$data['publish_up'] = JFactory::getDate($publishUpDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['publish_up'] = $data['created'];
			}
		}

		if (isset($data['publishDownDate']) && isset($data['publishDownTime']))
		{
			// Convert date to UTC
			$publishDownDateTime = $data['publishDownDate'].' '.$data['publishDownTime'];
			if ((int)$publishDownDateTime > 0)
			{
				$data['publish_down'] = JFactory::getDate($publishDownDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['publish_down'] = '';
			}
		}

		if (isset($data['startDate']) && isset($data['startTime']))
		{
			// Convert date to UTC
			$startDateTime = $data['startDate'].' '.$data['startTime'];
			if ((int)$startDateTime > 0)
			{
				$data['start_date'] = JFactory::getDate($startDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['start_date'] = '';
			}
		}

		if (isset($data['endDate']) && isset($data['endTime']))
		{
			// Convert date to UTC
			$endDateTime = $data['endDate'].' '.$data['endTime'];
			if ((int)$endDateTime > 0)
			{
				$data['end_date'] = JFactory::getDate($endDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['end_date'] = '';
			}
		}

	}

	/**
	 * onAfterSave method.
	 *
	 * @return void
	 */

	protected function onAfterSave()
	{
		$data = $this->getState('data');
		if (isset($data['tags']) && JString::trim($data['tags']) != '')
		{
			$model = K2Model::getInstance('Tags', 'K2Model');
			$itemId = $this->getState('id');
			$model->deleteItemTags($itemId);

			$tags = explode(',', $data['tags']);
			$tags = array_unique($tags);
			foreach ($tags as $tag)
			{
				$tagId = $model->addTag($tag);
				$model->tagItem($tagId, $itemId);
			}
		}

		if (isset($data['imageValue']) && $data['imageValue'] && $data['imageValue'] != $this->getState('id'))
		{
			$sizes = array(
				'XL' => 600,
				'L' => 400,
				'M' => 240,
				'S' => 180,
				'XS' => 100
			);

			require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
			$filesystem = K2FileSystem::getInstance();
			$baseSourceFileName = md5('Image'.$data['imageValue']);
			$baseTargetFileName = md5('Image'.$this->getState('id'));

			// Original image
			$path = 'media/k2/items/src';
			$source = $baseSourceFileName.'.jpg';
			$target = $baseTargetFileName.'.jpg';
			$filesystem->write($path.'/'.$target, $filesystem->read($path.'/'.$source), true);
			$filesystem->delete($path.'/'.$source);

			// Resized images
			$path = 'media/k2/items/cache';
			foreach ($sizes as $size => $width)
			{
				$source = $baseSourceFileName.'_'.$size.'.jpg';
				$target = $baseTargetFileName.'_'.$size.'.jpg';
				$filesystem->write($path.'/'.$target, $filesystem->read($path.'/'.$source), true);
				$filesystem->delete($path.'/'.$source);
			}

		}

		if (isset($data['attachments']))
		{

			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Attachments', 'K2Model');

			$ids = $data['attachments']['id'];
			$names = $data['attachments']['name'];
			$titles = $data['attachments']['title'];

			foreach ($ids as $key => $id)
			{
				$data = array();
				$data['id'] = $id;
				$data['name'] = $names[$key];
				$data['title'] = $titles[$key];
				$data['itemId'] = $this->getState('id');
				$model->setState('data', $data);
				$model->save();
			}

		}

	}

}
