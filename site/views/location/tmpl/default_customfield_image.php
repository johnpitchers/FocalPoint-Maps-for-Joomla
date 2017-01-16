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
    <p class="fp_customfield fp_image">
	<span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
	<?php } ?>
	<?php echo '<img src="'.JUri::base(true)."/".$this->outputfield->data.'" title="'.$this->outputfield->label.'" alt="'.$this->outputfield->label.'" />'; ?>

    <?php if (!$this->outputfield->hidelabel) { ?>
    </p>
    <?php } ?>
