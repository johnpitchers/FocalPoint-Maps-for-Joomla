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
class JFormFieldLocationtype extends JFormFieldPredefinedList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'locationtype';

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
            ->select('a.id, CONCAT("(",b.title,") ",a.title) AS title')
            ->from('#__focalpoint_locationtypes AS a')
            ->innerJoin('#__focalpoint_legends AS b on a.legend = b.id')
            ->where('a.state > -1')
            ->order('b.ordering,a.ordering');
        $db->setQuery($query);
        $results = $db->loadObjectList();
        foreach ($results as $result) {
            $this->predefinedOptions[$result->id] = $result->title;
        }
    }
}
