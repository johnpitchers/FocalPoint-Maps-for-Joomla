<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$showmapsearch = $this->item->params->get('mapsearchenabled');
$searchprompt = $this->item->params->get('mapsearchprompt');
$sidebar = ($this->item->params->get('legendposition') == "left" || $this->item->params->get('legendposition') == "right");
?>

<div class="row-fluid ">
	<p><small id="activecount"></small></p>
	<div id="fp_map_actions">
		<form onsubmit="return false;">
            <?php if ($showmapsearch) { ?>
            <div class="fp_mapsearch input-append">
                <label for="fp_searchAddress"><?php echo $searchprompt; ?></label>
                <input class="" id="fp_searchAddress" type="text" value="<?php echo $searchprompt; ?>" onblur="if (this.value=='') {this.value='<?php echo $searchprompt; ?>';jQuery('#fp_searchAddressBtn').attr('disabled', true);}" onfocus="if (this.value=='<?php echo $searchprompt; ?>') this.value='';jQuery('#fp_searchAddressBtn').attr('disabled', false);"><button class="btn " id="fp_searchAddressBtn" type="button" disabled >Go!</button>
            </div>
            <?php } ?>
            <div id="fp_map_buttons" class="input-append">
                <button class="btn btn-mini" id="fp_reset" onclick="return false;"><?php echo JText::_('COM_FOCALPOINT_BUTTON_RESET_MAP'); ?></button>
                <button class="btn btn-mini" id="fp_toggle" data-togglestate="on" onclick="return false;"></button>
            </div>
        </form>
	</div>
</div>