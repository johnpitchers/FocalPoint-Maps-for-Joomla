<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Focalpoint model.
 */
class FocalpointModelMap extends JModelForm
{
    var $_item = null;
    
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_focalpoint');

		// Load state from the request userState on edit or from the passed variable on default
        if (JFactory::getApplication()->input->get('layout') == 'edit') {
            $id = JFactory::getApplication()->getUserState('com_focalpoint.edit.map.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_focalpoint.edit.map.id', $id);
        }
		$this->setState('map.id', $id);

		// Load the parameters.
		$params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('map.id', $params_array['item_id']);
        }
		$this->setState('params', $params);

	}
        

	/**
	 * Method to get an ojbect.
	 *
	 * @param	integer	The id of the object to get.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		
        if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id)) {
                //Get the id from the URL. This maintains the same Itemid (menu item) when clicking from map to map
                $id = JFactory::getApplication()->input->get('id');

                if (empty($id)){
                    //Get the map id from the menu.
                    $id = $this->getState('map.id');
                }
                
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published) {
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
			} elseif ($error = $table->getError()) {
				$this->setError($error);
			}
		}
        
        //Format the tabs data for use in the template as an object.
        $this->_item->tabs = self::formatTabsData($this->_item->tabsdata);

		return $this->_item;
	}
    
    function formatTabsData($data) {
        // Decode the data from JSON
        $data = json_decode($data);
        return $data;
    }
    
	public function getTable($type = 'Map', $prefix = 'FocalpointTable', $config = array())
	{   
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        return JTable::getInstance($type, $prefix, $config);
	}     

    
	/**
	 * Method to check in an item.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int)$this->getState('map.id');

		if ($id) {
            
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int)$this->getState('map.id');
		if ($id) {
            
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}
		return true;
	}    

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
     *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return parent::getForm($data = array(), $loadData = true);
	}



    
    /**
	 * Method to get the sidebar data.
	 *
	 */
	public function getMarkerData($id = null)
	{

		// Load the component parameters.
		$app =JFactory::getApplication();
		$params = $app->getParams('com_focalpoint');

		// Grab all our required location info from the database as an object
        $db = JFactory::getDbo();

        //Check Multicategorisation plugin?
        //$multicategorisation = false;
        //if ($plugin = JPluginHelper::getPlugin('focalpoint','multicategorisation')){
        //    $params->set("multicategorisation",1);
        //    $multicategorisation = true;
        //}

        $multicategorisation = true;
        $params->set("multicategorisation",1);

		if ($multicategorisation) {
			$query ="
			SELECT c.title AS legend, c.subtitle AS legendsubtitle, c.alias AS legendalias,
			b.title AS locationtype, b.id as locationtype_id, b.alias AS locationtypealias, e.marker AS marker_type,
			a.id, a.state, a.title, a.alias, a.map_id, a.type, a.address, a.phone, a.description,
			a.customfieldsdata,
			a.latitude, a.longitude, a.marker AS marker_location, a.linktype, a.altlink, a.maplinkid, a.menulink, a.params,
			CONCAT('index.php?option=com_focalpoint&view=location&id=',a.id) AS link
			FROM #__focalpoint_locations AS a
			INNER JOIN #__focalpoint_locationtypes AS b
			INNER JOIN #__focalpoint_locationtypes AS e
			INNER JOIN #__focalpoint_legends AS c
			INNER JOIN #__focalpoint_location_type_xref AS d
			ON d.location_id = a.id
			AND d.locationtype_id = b.id
			AND e.id = a.type
			AND b.legend = c.id
			WHERE a.map_id = ". $id ." AND a.state = 1 AND b.state = 1 AND c.state = 1
			ORDER BY c.ordering, b.ordering
			";
		}

        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Let's do a little processing before passing the results back to the view
        // 
        // Cycle through the results and store the relevant marker icon in $result->marker.
        // The rule is as follows.
        //   1.Location marker (top priority)
        //   2.Location Type marker (second priority)
        //   3.Configuration default marker (third priority).
        //
        // If a maplink or URL link has been defined then overwrite $result->link. Saves extra processing in the template.
		// Do some extra processing on the link at the end.

		foreach ($results as $result){

			// Merge the item params and global params. For the maps view we only need the infobox parameters
			// but easier to merge them all anyway.
			$itemparams = new JRegistry;
			$itemparams->loadString($result->params, 'JSON');
			$result->params = $itemparams;

			// Merge global params with item params
			$newparams = clone $params;
			$newparams->merge($result->params);
			$result->params = $newparams;

            if ($result->marker_location) {
                $result->marker = JURI::base().$result->marker_location;
            } else {
                if ($result->marker_type){
                    $result->marker = JURI::base().$result->marker_type;
                } else {
                    $result->marker = JURI::base().$params->get('marker');
                }
            }

            unset($result->marker_location);
            unset($result->marker_type);
            switch($result->linktype){
                case "0":
                    $app = JFactory::getApplication();
                    $menu = $app->getMenu()->getActive();
					if ($menu) $result->link .="&Itemid=".$menu->id;
                    break;
                case "1":
                    if ($result->altlink) {
                        $result->link = $result->altlink;
                    } else {
						$app = JFactory::getApplication();
						$menu = $app->getMenu()->getActive();
						$result->link .="&Itemid=".$menu->id;
					}
                    break;
                case "2":
                    if ($result->maplinkid) {
                        $app = JFactory::getApplication();
                        $db=JFactory::getDbo();
						$query = $db->getQuery(true);
						$query->select('id');
						$query->from('#__menu');
						$query->where('link = "index.php?option=com_focalpoint&view=map" AND params LIKE "%{\"item_id\":\"'.$result->maplinkid.'\",%"');
						$db->setQuery($query);
						$itemid = $db->loadResult();
                        $result->link = 'index.php?option=com_focalpoint&view=map&id='.$result->maplinkid."&Itemid=";
                    }
                    break;
                case "3":
                    unset($result->link);
                    break;
                case "4":
                    if ($result->menulink) {
                        $result->link = JRoute::_(JFactory::getApplication()->getMenu()->getItem($result->menulink)->link."&Itemid=".$result->menulink,true);
                    }
            }

            unset($result->altlink);
            unset($result->maplink);

			//Replace || with <br> in the address. Allows the user to easily add linebreaks to the address field.
			$result->address = str_replace("||"," <br>", $result->address);

			//Route the location link.
			if (isset($result->link)){
				if (!strstr($result->link,"http://")){
					$result->link = JRoute::_($result->link);
				}
			}

            // Decode the custom field data
            if (!empty($result->customfieldsdata)){
                $data = json_decode($result->customfieldsdata);

                // Grab the location type record so we can match up the label. We don't save the labels with the data.
                // This is so we can change individaul labels at any time without having to update every record.
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('customfields')
                    ->from('#__focalpoint_locationtypes')
                    ->where('id = ' . $result->type);
                $db->setQuery($query);
                $fieldsettings = (json_decode($db->loadResult()));
                $result->customfields = New stdClass();
                foreach ($data as $field=>$value) {
                    $segments  = explode(".", $field);

                    // Before adding the custom field data to the results we first need to check field settings matches
                    // the data. This is required in case the admin changes or deletes a custom field
                    // from the location type but the data still exists in the location items record.
                    if (!empty($fieldsettings->{$segments[0].".".$segments[1]})){
                        $result->customfields->{end($segments)} = New stdClass();
                        $result->customfields->{end($segments)}->datatype = $segments[0];
                        $result->customfields->{end($segments)}->label = $fieldsettings->{$segments[0].".".$segments[1]}->label;
                        $result->customfields->{end($segments)}->data = $value;
                    }
                }
            }
            unset($result->customfieldsdata);
        }
        //Send it back to the template.
        return $results;    
    }
}