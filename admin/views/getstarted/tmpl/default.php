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

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_focalpoint/assets/css/focalpoint.css');
$task = JFactory::getApplication()->input->get('task', 'config');

?>
<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=legends'); ?>" method="post" name="adminForm"
      id="adminForm" class="fp_<?php echo $task; ?> tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?>">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>

            <div id="fp_pointer"></div>
            <div class="hero-unit" style="text-align:left;">
                <?php if ($task == 'config') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_CONFIG'); ?>
                <?php } ?>

                <?php if ($task == 'map') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_MAPS'); ?>
                <?php } ?>

                <?php if ($task == 'legend') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LEGENDS'); ?>
                <?php } ?>

                <?php if ($task == 'locationtype') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LOCATIONTYPES'); ?>
                <?php } ?>
            </div>

        </div>
</form>