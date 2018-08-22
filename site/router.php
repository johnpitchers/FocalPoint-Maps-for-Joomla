<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 *
 * Component Routes
 *
 *  http://root/{menu_alias}  <- if menu exists
 *  http://root/{menu_alias}/{location_alias} <- shows location view at menu id
 *  http://root/component/focalpoint/map/id <-- map view
 *  http://root/component/focalpoint/location/id <-- location view
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @param	array	A named array
 * @return	array
 */
function FocalpointBuildRoute(&$query)
{
	//die('hello builder');
	$segments = array();

	// get a menu item based on Itemid or currently active
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$params = JComponentHelper::getParams('com_focalpoint');

	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid']))
	{
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else
	{
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	}

	// check again
	if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_focalpoint')
	{
		$menuItemGiven = false;
		unset($query['Itemid']);
	}

	if (isset($query['view'])) {

		if($query['view'] =="map"){
			if (isset($query['id'])) {
				$db = JFactory::getDbo();
				$sql = $db->getQuery(true);
				$sql->select('alias');
				$sql->from('#__focalpoint_locations');
				$sql->where('id='.$query['id']);
				$db->setQuery($sql);
				$alias= $db->loadResult();
				unset($query['id']);
			}
		}

		if($query['view'] =="location"){
			if (isset($query['id'])) {
				$db = JFactory::getDbo();
				$sql = $db->getQuery(true);
				$sql->select('alias');
				$sql->from('#__focalpoint_locations');
				$sql->where('id='.$query['id']);
				$db->setQuery($sql);
				$alias= $db->loadResult();

				$segments[] = $alias;
				unset($query['id']);
			}
		}
		unset($query['view']);
	}

	//echo"<pre>"; print_r($params);echo "</pre>";echo"<pre>"; print_r($segments);echo "</pre>";
	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/focalpoint/task/id/Itemid
 *
 * index.php?/focalpoint/id/Itemid
 */
function FocalpointParseRoute($segments)
{
	$vars = array();
	//Get the active menu item.
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$item = $menu->getActive();

	//Check the active menu is a map link.
	// If so, the first segment should be a location alias
	if ($item){
		if ($item->query['view'] =="map") {

			$alias = implode("-",explode(":",$segments[0]));
			$db = JFactory::getDbo();
			$sql = $db->getQuery(true);
			$sql->select('id');
			$sql->from('#__focalpoint_locations');
			$sql->where('alias ="'.$alias.'"');
			$db->setQuery($sql);
			$id = $db->loadResult();

			if (isset($id)){
				$vars['view'] = "location";
				$vars['id'] = $id;
			} else {
				echo "unknown alias";
			}

		}
	} else {

		//Oh no! We don't have an active menu. better find one.
		$type 		= $segments[0];
		$item_id 	= $segments[1];
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true);
		$sql->select('id');
		$sql->from('#__menu');
		$sql->where('link LIKE "%option=com_focalpoint&view='.$type.'%" AND params LIKE "%item_id\":\"'.$item_id.'\"%" AND published = 1');
		$db->setQuery($sql);
		$menuitem = $db->loadResult();

		if(!$menuitem) {
			//Still no active menu.
			//Check if it's a location. If so, is the parent map linked to a menu?
			if ($type =="location") {

				//We need the map_id this location is assigned to.
				$sql = $db->getQuery(true);
				$sql->select('map_id');
				$sql->from('#__focalpoint_locations');
				$sql->where('id = '.$item_id);
				$db->setQuery($sql);
				$map_id = $db->loadResult();

				//Check if it is linked to a published menu.
				if ($map_id){
					$sql = $db->getQuery(true);
					$sql->select('id');
					$sql->from('#__menu');
					$sql->where('link LIKE "%option=com_focalpoint&view=map%" AND params LIKE "%item_id\":\"'.$map_id.'\"%" AND published = 1');
					$db->setQuery($sql);
					$menuitem = $db->loadResult();
				}
			}
		}
		if ($menuitem) {
			$vars['Itemid'] = $menuitem;
			$vars['view'] = $type;
			$vars['id']= $item_id;
		} else {
			//Oh well! We tried. No menu exists. Just pass what we have. No Itemid for you!
			$vars['view'] = $type;
			$vars['id']= $item_id;
		}
	}

	//echo"<pre>";print_r($menuitem);echo"</pre>";
	//echo"<pre>";print_r($item);echo"</pre>";
	//echo"<pre>";print_r($segments);echo"</pre>";
	//echo"<pre>";print_r($vars);echo"</pre>";
	//die('hello parser');

	return $vars;
}
