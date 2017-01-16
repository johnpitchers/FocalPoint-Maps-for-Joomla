<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Focalpoint records.
 */
class FocalpointModellocations extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'title', 'a.title',
                'map_title', 'map_title',
                'type', 'a.type', //the location type. Need to change this.
                'alias', 'a.alias',
                'description', 'a.description',
                'address', 'a.address',
                'marker', 'a.marker',
                'keylocation', 'a.keylocation',
                'customfieldsdata', 'a.customfieldsdata',
                'created_by', 'a.created_by',
                'params', 'a.params',
                'locationtype_title', 'locationtype_title'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);


        //Filtering map_id
        $this->setState('filter.map_id', $app->getUserStateFromRequest($this->context . '.filter.map_id', 'filter_map_id', '', 'string'));

        //Filtering type
        $this->setState('filter.type', $app->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));


        // Load the parameters.
        $params = JComponentHelper::getParams('com_focalpoint');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.title', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param    string $id A prefix for the store id.
     * @return    string        A store id.
     * @since    1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return    JDatabaseQuery
     * @since    1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from('`#__focalpoint_locations` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the category 'map_id'
        $query->select('map_title.title AS map_title');
        $query->join('LEFT', '#__focalpoint_maps AS map_title ON map_title.id = a.map_id');

        // Join over the foreign key 'type'
        $query->select('c.title AS locationtype_title');
        $query->join('LEFT', '#__focalpoint_locationtypes AS c ON c.id = a.type');

        // Join over the user field 'created_by'
        $query->select('created_by.name AS created_by');
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int)$published);
        } else if ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.title LIKE ' . $search . '  OR  a.description LIKE ' . $search . '  OR  a.address LIKE ' . $search . ' )');
            }
        }

        //Filtering created_by
        $filter_created_by = $this->state->get("filter.created_by");
        if ($filter_created_by) {
            $query->where("a.created_by = '" . $db->escape($filter_created_by) . "'");
        }

        //Filtering map_id
        $filter_map_id = $this->state->get("filter.map_id");
        if ($filter_map_id) {
            $query->where("a.map_id = '" . $db->escape($filter_map_id) . "'");
        }

        //Filtering type
        $filter_type = $this->state->get("filter.type");
        if ($filter_type) {
            $query->where("a.type = '" . $db->escape($filter_type) . "'");
        }


        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }
}
