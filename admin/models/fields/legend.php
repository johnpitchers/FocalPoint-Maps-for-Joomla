<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Form Field to load a list of states
 * Used in place of the Joomla status field as FocalPoint does not use "archived"
 */
class JFormFieldLegend extends JFormFieldPredefinedList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'legend';

    /**
     * Available statuses
     *
     * @var  array
     * @since  3.2
     */
    protected $predefinedOptions = array();

    /**
     * Constrct method to get the field input markup.
     */
    function __construct()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('id,title')
            ->from('`#__focalpoint_legends`')
            ->where('`state` > -1');
        $db->setQuery($query);
        $results = $db->loadObjectList();
        foreach ($results as $result) {
            $this->predefinedOptions[$result->id] = $result->title;
        }
    }
}
