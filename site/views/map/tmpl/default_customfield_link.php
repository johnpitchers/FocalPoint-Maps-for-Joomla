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
    <p class="fp_customfield fp_link">
    <span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
	<?php }?>
    <a href="<?php echo $this->outputfield->data->url; ?>" target="<?php echo $this->outputfield->data->target?"_blank":"_self"; ?>">
        <?php echo ($this->outputfield->data->linktext!="")?$this->outputfield->data->linktext:$this->outputfield->data->url; ?>
    </a>
    <?php if (!$this->outputfield->hidelabel) { ?>
    </p>
    <?php } ?>
