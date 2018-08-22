<?php
/**
 * @version     1.1.1
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 *
 * A note for template designers.
 *
 * To output a customfield with a label use
 * 		$this->renderCustomField("your-field-name", $hidelabel, $buffer);
 *  	$hidelabel is TRUE or FALSE
 *      $buffer is TRUE or FALSE. If TRUE the output is buffered and returned. If FALSE it is output directly.
 *
 * Alternatively iterate through the object $this->item->customfields AS $field and call
 *  	$this->renderField($field, $hidelabel, $buffer);
 *
 */

// no direct access
defined('_JEXEC') or die;

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_focalpoint', JPATH_ADMINISTRATOR);

// Load the default CSS/JS files.
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_focalpoint/assets/css/focalpoint.css');
if ( $this->item->params->get('loadBootstrap') ) {
	$document->addStyleSheet('components/com_focalpoint/assets/css/bootstrap.css');
	JHtml::_('bootstrap.framework');
}
?>

<div id="focalpoint" class="fp-location-view">
	<div class="row-fluid">
	<?php if (isset($this->item->page_title)) { ?>
		<h1><?php echo $this->item->page_title; ?></h1>
		<h2 class="<?php echo $this->item->backlink?"backlink":""; ?>">
		<?php echo $this->item->backlink?'<a class="backtomap" href="'.$this->item->backlink.'">'.JText::_('COM_FOCALPOINT_BACK_TO_MAP').'</a>':"";
		echo $this->item->title; ?>
		</h2>
	<?php } else { ?>
		<h1 class="<?php echo $this->item->backlink?"backlink":""; ?>">
		<?php echo $this->item->backlink?'<a class="backtomap" href="'.$this->item->backlink.'">'.JText::_('COM_FOCALPOINT_BACK_TO_MAP').'</a>':"";
		echo trim($this->item->title); ?>
		</h1>
	<?php } ?>
	</div>
	<div class="row-fluid">
		<div class="fp_left_column span8">
            <div id="fp_googleMap"></div>
            <?php if ($this->item->params->get('getdirections')) { ?>
                <div id="fp_googleMap_directions"></div>
                <div id="fp_map_actions" class="input-append">
                    <form onsubmit="return false;"><label for="fp_searchAddress"><?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?></label>
                        <input class="" id="fp_searchAddress" type="text" value="<?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>" onblur="if (this.value=='') {this.value='<?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>';jQuery('#fp_searchAddressBtn').attr('disabled', true);}" onfocus="if (this.value=='<?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>') this.value='';jQuery('#fp_searchAddressBtn').attr('disabled', false);">
                        <button class="btn " id="fp_searchAddressBtn" type="submit" disabled ><?php echo JText::_('COM_FOCALPOINT_GET_DIRECTIONS'); ?></button>
                    </form>
                </div>
            <?php } ?>

			<?php if (!$this->item->params->get('hideintrotext')) { ?>
				<?php echo $this->item->description; ?>
			<?php } ?>
			<?php echo $this->item->fulldescription; ?>


            <?php
            /**
             * Custom fields.
             */
            ?>
			<?php if (count($this->item->customfields)) { ?>
				<div class="fp_customfields fp_content">
					<?php foreach ($this->item->customfields as $key=>$customfield) { ?>
						<?php $this->renderField($customfield); ?>
					<?php } ?>
				</div>
			<?php } ?>
            <?php
            /**
             * End custom fields.
             */
            ?>

		</div>

		<div class="fp_right_column span4">
            <?php if ($this->item->address || $this->item->phone) { ?>
                <div class="row-fluid fp_address">
                    <?php if ($this->item->address) { ?>
                        <div class="span12">
                            <h3><?php echo JText::_('COM_FOCALPOINT_ADDRESS'); ?>:</h3>
                            <p><?php echo $this->item->address; ?></p>
                        </div>
                    <?php }?>
                    <?php if ($this->item->phone) { ?>
                        <div class="span12">
                            <h3><?php echo JText::_('COM_FOCALPOINT_PHONE'); ?>:</h3>
                            <p><?php echo $this->item->phone; ?></p>
                        </div>
                    <?php }?>
                </div>
            <?php } ?>

            <?php if($this->item->image) { ?>
                <div class="fp_article_image">
                    <p><img src="<?php echo $this->item->image; ?>" title=""/></p>
                </div>
            <?php } ?>

		</div>
	</div>
	<div class="row-fluid">
	<?php if ($this->item->backlink) { ?>
	<p>
		<a class="btn backtomap" href="<?php echo $this->item->backlink; ?>"><?php echo JText::_('COM_FOCALPOINT_BACK_TO_MAP')?></a>
	</p>
	<?php } ?>
	</div>
	<?php echo $this->loadTemplate('mapjs'); ?>
	<?php if (JFactory::getApplication()->input->get("debug")) {echo "<pre>"; print_r($this->item); echo"</pre>";} ?>

</div>