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
class FocalpointModelLocation extends JModelForm
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
            $id = JFactory::getApplication()->getUserState('com_focalpoint.edit.location.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_focalpoint.edit.location.id', $id);
        }
		$this->setState('location.id', $id);

		// Load the parameters.
		$params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('location.id', $params_array['item_id']);
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
                    $id = $this->getState('location.id');
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
        
        //Format the customfields data for use in the template as an object.
        $this->_item->customfieldsdata = self::formatCustomFields($this->_item->customfieldsdata);
        
        // Determine the correct marker to use before returning the data.
        $this->_item->marker = self::getMarker($id, $this->_item->marker);

		//Replace || with <br> in the address. Allows the user to easily add linebreaks to the address field.
		$this->_item->address = str_replace("||"," <br>", $this->_item->address);

		// Determine the correct map link.
		$this->_item->backlink = self::getBackLink($this->_item->map_id);

		return $this->_item;
	}

    function getItemid($location_id){
        $link 	= "index.php?option=com_focalpoint&view=map";
        $db		= JFactory::getDbo();
        $query	= $db->getQuery(true);
        $query->select("id,params")
            ->from("#__menu")
            ->where("link=\"".$link."\" AND published=1");
        $db->setQuery($query);
        $results= $db->loadObjectList();
        foreach($results as $result) {
            $this_params = new JRegistry($result->params);
            if ($this_params->get('item_id') == $location_id){
                return($result->id);
            }
        }
        return false;
    }

	function getBackLink($location_id){
        if ($itemid = $this->getItemid($location_id)){
            return("index.php?option=com_focalpoint&view=map&Itemid=".$itemid);
        }
		return false;
	}

    function formatCustomFields($data) {
        
        if (empty ($data)) {
            // Declare the customfields property to avoid getting PHP notices in the tempate.
            $this->_item->customfields = NULL;
            return false;
        }
        
        // Decode the data from JSON
        $data = json_decode($data);
        
        // Grab the location type record so we can match up the label. We don't save the labels with the data.
        // This is so we can change individaul labels at any time without having to update every record.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query 
            ->select('customfields')
            ->from('#__focalpoint_locationtypes')
            ->where('id = ' . $this->_item->type);
        $db->setQuery($query);
        $fieldsettings = (json_decode($db->loadResult()));
        
        // Remove the id numbers from the field. turns [textbox.1234567.thisfield] into [thisfield]
        // Create a new object for each field containing the datatype, label and data.
		$this->_item->customfields = New stdClass();
        foreach ($data as $field=>$value) {
            $segments  = explode(".", $field);

            // Before adding the custom field data to the results we first need to check field settings matches
            // the data. This is required in case the admin changes or deletes a custom field
            // from the location type but the data still exists in the location items record.
            if (!empty($fieldsettings->{$segments[0].".".$segments[1]})){
                $this->_item->customfields->{end($segments)} = New stdClass();
                $this->_item->customfields->{end($segments)}->datatype = $segments[0];
                $this->_item->customfields->{end($segments)}->label = $fieldsettings->{$segments[0].".".$segments[1]}->label;
                $this->_item->customfields->{end($segments)}->data = $value;
            }
        }
    }
    
    function getMarker($id, $location_marker) {
        $marker = $location_marker;
        // This marker has been defined at the location level. It rules!
        
        if (!$marker) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query 
                ->select('a.marker')
                ->from('#__focalpoint_locationtypes AS a')
                ->leftJoin('#__focalpoint_locations AS b ON b.type= a.id')
                ->where('b.id = ' . $id);
            $db->setQuery($query);
            $marker = $db->loadResult();
        }
        
        if (!$marker) {
            // Fallback onto the component parameters. The parameters have already been merged in the view.
            // If a marker has been set in the map settings or global option it will defined in $params.
            $app =JFactory::getApplication();
            $params = $app->getParams('com_focalpoint'); 
            $marker = $params->get('marker');
        }
        
        if ($marker) {
            $marker= JURI::base().$marker;
        }
        
        return $marker;
    }
    
	public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = array())
	{   
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        return JTable::getInstance($type, $prefix, $config);
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


    
}