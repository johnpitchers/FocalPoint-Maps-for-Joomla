<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */


// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_focalpoint')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// Register the Helper class
JLoader::register('FocalpointHelper', __DIR__ . '/helpers/focalpoint.php');

// Register the mapsAPI class to handle geocoding and map functions.
JLoader::register('mapsAPI', __DIR__ . '/helpers/maps.php');

$controller = JControllerLegacy::getInstance('Focalpoint');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
