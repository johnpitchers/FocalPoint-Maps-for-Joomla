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

jimport('joomla.application.component.modeladmin');

/**
 * Focalpoint model.
 */
class FocalpointModellocation extends JModelAdmin
{
    /**
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'COM_FOCALPOINT';


    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param    type    The table type to instantiate
     * @param    string    A prefix for the table class name. Optional.
     * @param    array    Configuration array for model. Optional.
     * @return    JTable    A database object
     * @since    1.6
     */
    public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param    array $data An optional array of data for the form to interogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm('com_focalpoint.location', 'location', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_focalpoint.edit.location.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            //Support for multiple or not foreign key field: type
            $array = array();
            foreach ((array)$data->type as $value):
                if (!is_array($value)):
                    $array[] = $value;
                endif;
            endforeach;
            $data->type = implode(',', $array);
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param    integer    The id of the primary key.
     *
     * @return    mixed    Object on success, false on failure.
     * @since    1.6
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {

            //Do any procesing on fields here if needed

            // Merge the intro and full text.
            $item->description = trim($item->fulldescription) != '' ? $item->description . "<hr id=\"system-readmore\" />" . $item->fulldescription : $item->description;

            // Convert the othertypes list back into an array
            $registry = new JRegistry;
            $registry->loadString($item->othertypes);
            $item->othertypes = $registry->toArray();

            //JSON decode the custom fields so it gets sent to the View as an assoc array.
            if (isset($item->customfieldsdata)) {
                $item->custom = json_decode($item->customfieldsdata, true);
                if (isset($item->custom)) {
                    foreach ($item->custom as $key => $value) {
                        $value = self::stripslashes_extended($value);

                    }
                }
            }

            // Convert the metadata field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();


        }
        return $item;
    }

    private function stripslashes_extended(&$arr_r)
    {
        if (is_array($arr_r)) {
            foreach ($arr_r as &$val) {
                is_array($val) ? stripslashes_extended($val) : $val = stripslashes($val);
            }
            unset($val);
        } else {
            $arr_r = stripslashes($arr_r);
        }
        return $arr_r;
    }

    function get_calling_class()
    {

        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) // is it set?
                if ($class != $trace[$i]['class']) // is it a different class
                    return $trace[$i]['class'];
        }
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        jimport('joomla.filter.output');
        //Fix the alias before saving.
        if ($table->alias) {
            $table->alias = JFilterOutput::stringURLSafe($table->alias);
        } else {
            $table->alias = JFilterOutput::stringURLSafe($table->title);
        }

        // Split the description into two parts if required.
        if (strstr($table->description, '<hr id="system-readmore" />')) {
            $fulltext = explode('<hr id="system-readmore" />', $table->description);
            $table->description = trim($fulltext[0]);
            $table->fulldescription = trim($fulltext[1]);
        } else {
            $table->fulldescription = '';
        }

        // Geocode the geoaddress field into Latitude/Longitude.
        // Geoaddress field has been removed as this is now handled live via Javascript.
        // Will leave the code here as it may come in handy for a future addition.
        //if (!empty($table->geoaddress) && (($table->latitude == "0" && $table->longitude =="0") || ($table->latitude == "" && $table->longitude ==""))){

        //Create a new mapsAPI object.
        //    $mapsAPI            = new mapsAPI();

        // Geocode the geoaddress field.
        //    $latLong            = $mapsAPI->getLatLong($table->geoaddress);

        // Assign the lat long coords to the table for saving in the database.
        //    $table->latitude    =$latLong[0];
        //    $table->longitude   =$latLong[1];
        //}

        if (empty($table->id)) {

            // Set ordering to the last item if not set
            if (@$table->ordering === '') {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__focalpoint_locations');
                $max = $db->loadResult();
                $table->ordering = $max + 1;
            }
        }
    }

    public function getCustomFieldsHTML($type)
    {
        if (!isset($type)) {
            return false;
        }

        //Retrieve the customfields for the relevant location type. There will be only one result.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('customfields');
        $query->from('#__focalpoint_locationtypes');
        $query->where('id=' . $type);
        $db->setQuery($query);

        //Decode the data so fields are returned as an object
        $result = json_decode($db->loadResult(), true);

        return $result;
    }

    public function toJSON($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::toJSON($value);
            } else {
                $value = addslashes($value);
            }
        }
        return json_encode($data);
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   12.2
     */
    public function save($data)
    {

        $params = JComponentHelper::getParams('com_focalpoint');
        // We are going to save our 'othertypes' data in a separate table then hand control back to
        // the parent function.
        $id = $data['id'];
        $db = JFactory::getDbo();

        // If the user hasn't included the primary type then lets add it here. Makes the frontend sql
        // much easier if all categories are in the one table.
        if (!in_array($data['type'], $data['othertypes'])) $data['othertypes'][] = $data['type'];

        //Delete all xrefs before saving new.
        $sql = $db->getQuery(true);
        $sql->delete('#__focalpoint_location_type_xref');
        $sql->where('location_id = ' . $id);
        $db->setQuery($sql);
        $db->execute();

        $datasave = parent::save($data);

        //Get the last used id
        if (!isset($id) || $id == "") $id = $db->insertid();

        // Insert xrefs from this save. This is to cross ref location types against this location.
        foreach ($data['othertypes'] as $type) {
            $sql = $db->getQuery(true);
            $sql->insert('#__focalpoint_location_type_xref');
            $sql->values('NULL,' . $id . ',' . $type);
            $db->setQuery($sql);
            $db->execute();
        }
        return $datasave;

    }
}