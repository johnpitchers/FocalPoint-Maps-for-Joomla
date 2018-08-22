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
class FocalpointModellocationtype extends JModelAdmin
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
    public function getTable($type = 'Locationtype', $prefix = 'FocalpointTable', $config = array())
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
        $form = $this->loadForm('com_focalpoint.locationtype', 'locationtype', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_focalpoint.edit.locationtype.data', array());

        if (empty($data)) {
            $data = $this->getItem();


            //Support for multiple or not foreign key field: legend
            $array = array();
            foreach ((array)$data->legend as $value):
                if (!is_array($value)):
                    $array[] = $value;
                endif;
            endforeach;
            $data->legend = implode(',', $array);
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

            //JSON decode the custom fields so it gets sent to the View as an assoc array.
            if (isset($item->customfields)) {
                $item->custom = json_decode($item->customfields, true);
                $item->customfields = "";
            }
            //Do any procesing on fields here if needed
            //FocalpointHelper::printNdie($item);
        }

        return $item;
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

        if (empty($table->id)) {

            // Set ordering to the last item if not set
            if (@$table->ordering === '') {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__focalpoint_locationtypes');
                $max = $db->loadResult();
                $table->ordering = $max + 1;
            }

        }


    }

    public function toJSON($data)
    {
        return json_encode($data);
    }

}