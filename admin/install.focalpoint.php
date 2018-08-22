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

/**
 * Installation script for FocalPoint
 */
class com_focalpointInstallerScript
{
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent)
    {
    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent)
    {
    }

    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent)
    {
        // $parent is the class calling this method
        echo '<p>' . JText::sprintf('FocalPoint has been successfully updated.') . '</p>';
    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent)
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
    }

    /**
     * Runs after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent)
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)

        //Move the markers to the images folder on new install only
        if ($type == 'install') {
            $markers_moved = JFile::move(JPATH_SITE . "/media/com_focalpoint/markers", JPATH_SITE . "/images/markers");
            if ($markers_moved) {
                echo "<p>Successully moved markers to " . JPATH_SITE . "/images/markers/.";
            } else {
                echo "<p>Unable to move the markers folder to your /images folder. This is usaully due to;</p><ol><li>incorrect file permission settings. Please go to System > System Information > Directory Permissions and check that the images, media and tmp folders are writable.</li><li>You already have an /images/markers folder.</li></ol> ";
            }
        }

    }
}