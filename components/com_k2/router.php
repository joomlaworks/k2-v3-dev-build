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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';

/**
 * Build the route for the K2 component
 *
 * @return  array  An array of URL arguments
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 * @since    1.5
 */

function K2BuildRoute(&$query)
{
	// Initialize segments
	$segments = array();

	// If we are viewing a menu item check if it is the current one. If it is remove all it's variables
	if (!empty($query['Itemid']))
	{
		// Get application
		$application = JFactory::getApplication();

		// Get menu
		$menu = $application->getMenu();

		// Get active
		$active = $menu->getItem($query['Itemid']);

		// Assume that this is our menu item
		$match = true;

		// Check that all variables match
		foreach ($active->query as $key => $value)
		{
			// The variable of the menu does not exist in query. Don't match and break
			if (!isset($query[$key]))
			{
				$match = false;
				break;
			}

			// Check for numeric values ( for example when id contains alias )
			$checkedValue = is_numeric($value) ? (int)$query[$key] : $query[$key];

			// The variable of the menu does exist in query but has different value. Don't match and break
			if ($checkedValue != $value)
			{
				$match = false;
				break;
			}
		}

		// If the menu item is verified unset the common query variables. Keep only Itemid and option
		if ($match)
		{
			foreach ($active->query as $key => $value)
			{
				if ($key != 'Itemid' && $key != 'option')
				{
					unset($query[$key]);
				}
			}
		}
	}

	if (isset($query['view']))
	{
		$view = $query['view'];
		$segments[] = $view;
		unset($query['view']);
	}
	if (isset($query['task']))
	{
		$task = $query['task'];
		$segments[] = $task;
		unset($query['task']);
	}

	if (isset($query['id']))
	{
		$id = $query['id'];
		$segments[] = $id;
		unset($query['id']);
	}
	if (isset($query['year']))
	{
		$year = $query['year'];
		$segments[] = $year;
		unset($query['year']);
	}
	if (isset($query['month']))
	{
		$month = $query['month'];
		$segments[] = $month;
		unset($query['month']);
	}
	if (isset($query['day']))
	{
		$day = $query['day'];
		$segments[] = $day;
		unset($query['day']);
	}
	if (isset($query['hash']))
	{
		$hash = $query['hash'];
		$segments[] = $hash;
		unset($query['hash']);
	}

	$params = JComponentHelper::getParams('com_k2');
	if ($params->get('k2Sef') && count($segments))
	{
		K2AdvancedSEFBuild($segments);
	}

	return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @return  array  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 * @since    1.5
 */

function K2ParseRoute($segments)
{
	$vars = array();
	$vars['view'] = $segments[0];

	if ($vars['view'] == 'itemlist')
	{
		$vars['task'] = $segments[1];
		switch($vars['task'])
		{
			case 'category' :
			case 'tag' :
			case 'user' :
			case 'module' :
				if (isset($segments[2]))
				{
					$vars['id'] = $segments[2];
				}
				break;
			case 'date' :
				if (isset($segments[2]))
				{
					$vars['year'] = $segments[2];
				}
				if (isset($segments[3]))
				{
					$vars['month'] = $segments[3];
				}
				if (isset($segments[4]))
				{
					$vars['day'] = $segments[4];
				}
				if (isset($segments[5]))
				{
					$vars['categories'] = $segments[5];
				}
				break;
		}

	}
	else if ($vars['view'] == 'item')
	{
		$vars['id'] = $segments[1];

	}
	else if ($vars['view'] == 'attachments')
	{
		$vars['id'] = $segments[2];
		$vars['hash'] = $segments[3];
	}

	$params = JComponentHelper::getParams('com_k2');
	if ($params->get('k2Sef') && count($vars))
	{
		K2AdvancedSEFParse($vars, $segments);
	}

	return $vars;
}

function K2AdvancedSEFBuild(&$segments)
{
	$params = JComponentHelper::getParams('com_k2');
	$view = $segments[0];
	if ($view == 'itemlist')
	{
		$task = $segments[1];
		switch($task)
		{
			case 'category' :
				// Replace itemlist with the categories prefix
				$segments[0] = $params->get('k2SefLabelCat', 'content');

				// Remove the task completely
				unset($segments[1]);

				// Are we using the id in the URL?
				if ($params->get('k2SefInsertCatId'))
				{
					// If category alias is used in the URL. Check the desired separator
					if ($params->get('k2SefUseCatTitleAlias'))
					{
						// If the desired separator is slash, then apply it
						if ($params->get('k2SefCatIdTitleAliasSep') == 'slash')
						{
							$segments[2] = str_replace(':', '/', $segments[2]);
						}
					}
					// Category alias is not used in the URL. Keep only the numeric Id
					else
					{
						$segments[2] = (int)$segments[2];
					}
				}
				// Id will not be used in URL
				else
				{
					// Try to split the slug
					list($id, $alias) = explode(':', $segments[2]);
					
					// Use only alias
					$segments[2] = $alias;
				}

				break;
			case 'tag' :
				unset($segments[1]);
				$segments[0] = $params->get('k2SefLabelTag', 'tag');
				break;
			case 'user' :
				$segments[0] = $params->get('k2SefLabelUser', 'author');
				unset($segments[1]);
				break;
			case 'date' :
				$segments[0] = $params->get('k2SefLabelDate', 'date');
				unset($segments[1]);
				break;
			case 'search' :
				$segments[0] = $params->get('k2SefLabelSearch', 'search');
				unset($segments[1]);
				break;
		}
	}
	else if ($view == 'item')
	{
		// Items category prefix
		if ($params->get('k2SefLabelItem'))
		{
			// Replace the item with the category slug
			if ($params->get('k2SefLabelItem') == '1')
			{
				$item = K2Items::getInstance($segments[1]);
				$segments[0] = $item->category->alias;
			}
			else
			{
				$segments[0] = $params->get('k2SefLabelItemCustomPrefix');
			}
		}
		// Remove "item" from the URL
		else
		{
			unset($segments[0]);
		}

		// Handle item id and alias
		if ($params->get('k2SefInsertItemId'))
		{
			if ($params->get('k2SefUseItemTitleAlias'))
			{
				if ($params->get('k2SefItemIdTitleAliasSep') == 'slash')
				{
					$segments[1] = str_replace(':', '/', $segments[1]);
				}
			}
			else
			{
				$segments[1] = (int)$segments[1];
			}
		}
		// Id will not be used in URL
		else
		{
			// Try to split the slug
			list($id, $alias) = explode(':', $segments[1]);

			// Use only alias
			$segments[1] = $alias;
		}

	}

	// Reorder segments array
	$segments = array_values($segments);

}

function K2AdvancedSEFParse(&$vars, $segments)
{
	$params = JComponentHelper::getParams('com_k2');
	$reservedViews = array(
		'item',
		'itemlist'
	);

	if (!in_array($segments[0], $reservedViews))
	{
		// Category view
		if ($segments[0] == $params->get('k2SefLabelCat', 'content'))
		{
			$vars['view'] = 'itemlist';
			$vars['task'] = 'category';
			// Detect category id
			if ($params->get('k2SefInsertCatId'))
			{
				$vars['id'] = (int)$segments[1];
			}
			else
			{
				$category = K2Categories::getInstance($segments[1]);
				$vars['id'] = $category->id;
			}
		}
		// Tag view
		elseif ($segments[0] == $params->get('k2SefLabelTag', 'tag'))
		{
			$vars['view'] = 'itemlist';
			$vars['task'] = 'tag';
			$vars['id'] = $segments[2];
		}
		// User view
		elseif ($segments[0] == $params->get('k2SefLabelUser', 'author'))
		{
			$vars['view'] = 'itemlist';
			$vars['task'] = 'user';
			$vars['id'] = $segments[2];
		}
		// Date view
		elseif ($segments[0] == $params->get('k2SefLabelDate', 'date'))
		{
			$vars['view'] = 'itemlist';
			$vars['task'] = 'date';
		}
		// Search view
		elseif ($segments[0] == $params->get('k2SefLabelSearch', 'search'))
		{
			$vars['view'] = 'itemlist';
			$vars['task'] = 'search';
		}
		// Item view
		else
		{
			$vars['view'] = 'item';

			// Reinsert item id to the item alias
			if (!$params->get('k2SefInsertItemId'))
			{
				$alias = str_replace(':', '-', $segments[1]);
				$item = K2Items::getInstance($alias);
				$vars['id'] = $item->id.':'.$alias;
			}
			else
			{
				$vars['id'] = $segments[1];
			}
		}
	}
}