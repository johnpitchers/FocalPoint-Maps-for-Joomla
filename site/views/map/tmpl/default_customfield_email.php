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

<p class="fp_customfield fp_email">
	<?php if (!$this->outputfield->hidelabel) { ?>
	<span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
    <?php } ?>
	<a href="mailto:<?php echo $this->outputfield->data->email; ?>" >
        <?php echo ($this->outputfield->data->linktext!="")?$this->outputfield->data->linktext:$this->outputfield->data->email; ?>
    </a>
</p>