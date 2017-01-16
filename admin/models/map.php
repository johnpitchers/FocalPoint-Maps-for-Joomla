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
class FocalpointModelmap extends JModelAdmin
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
    public function getTable($type = 'Map', $prefix = 'FocalpointTable', $config = array())
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
        $form = $this->loadForm('com_focalpoint.map', 'map', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_focalpoint.edit.map.data', array());

        if (empty($data)) {
            $data = $this->getItem();

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

            //JSON decode the tabsdata so it gets sent to the View as an assoc array.
            //if (isset($item->tabsdata)){
            //    $item->tabs = json_decode($item->tabsdata,true);
            //    if (isset($item->tabs)){
            //        foreach ($item->tabs as $key=>$value){
            //            $value = self::stripslashes_extended($value);

            //        }
            //    }
            //}

            $registry = new JRegistry;
            $registry->loadString($item->tabsdata);
            $item->tabs = $registry->toObject();

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


        // Geocode the centerpoint field into Latitude/Longitude.
        // No longer required as of Build 0.9.1. 27 March 2013. Have left here just in case there's issues
        // with the modal popup on the maps screen for some users. We may have to revert to this.

        //if (!empty($table->centerpoint) && (($table->latitude == "0" && $table->longitude =="0") || ($table->latitude == "" && $table->longitude ==""))){

        //Create a new mapsAPI object.
        //    $mapsAPI            = new mapsAPI();

        // Geocode the geoaddress field.
        //    $latLong            = $mapsAPI->getLatLong($table->centerpoint);

        // Assign the lat long coords to the table for saving in the database.
        //    $table->latitude    =$latLong[0];
        //    $table->longitude   =$latLong[1];
        //}

        if (empty($table->id)) {

            // Set ordering to the last item if not set
            if (@$table->ordering === '') {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__focalpoint_maps');
                $max = $db->loadResult();
                $table->ordering = $max + 1;
            }

        }
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

}