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
?>
<?php if (!$this->outputfield->hidelabel) { ?>
<p class="fp_customfield fp_selectlist">
<?php } ?>
    <?php $first = true; ?>
    <?php foreach ($this->outputfield->data as $data) { ?>
	    <?php if (!$this->outputfield->hidelabel) { ?>
            <?php if (!$first){?>
                <br />
            <?php } ?>
	        <span class="fp_label"><?php echo $first?($this->outputfield->label.": "):" "; ?></span>
            <?php $first = false; ?>
	    <?php } ?>
	    <?php echo $data; ?>
    <?php } ?>
<?php if (!$this->outputfield->hidelabel) { ?>
</p>
<?php } ?>
