<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 *
 * A note for template designers.
 *
 * To output a customfield with a label use
 * 		$this->renderCustomField("your-field-name", $hidelabel);
 *  	$hidelabel is TRUE or FALSE
 *
 * Alternatively iterate through the $this->item->customfields object and call
 *  	$this->renderField($field,$hidelabel);
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
	$document->addStyleSheet(JURI::base().'components/com_focalpoint/assets/css/bootstrap.css');
	JHtml::_('bootstrap.framework');
}
?>

<div id="focalpoint" class="fp-location-view">
	<div class="row-fluid">
	<?php if (isset($this->item->page_title)) { ?>
		<h1><?php echo $this->item->page_title; ?></h1>
		<h2 class="<?php echo $this->item->backlink?"backlink":""; ?>">
		<?php echo $this->item->backlink?'<a class="backtomap" href="'.$this->item->backlink.'">Back to map</a>':"";
		echo $this->item->title; ?>
		</h2>
	<?php } else { ?>
		<h1 class="<?php echo $this->item->backlink?"backlink":""; ?>">
		<?php echo $this->item->backlink?'<a class="backtomap" href="'.$this->item->backlink.'">Back to map</a>':"";
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
                    <form onsubmit="return false;"><label for="fp_searchAddress">Your address</label>
                        <input class="" id="fp_searchAddress" type="text" value="Your address" onblur="if (this.value=='') {this.value='Your address';jQuery('#fp_searchAddressBtn').attr('disabled', true);}" onfocus="if (this.value=='Your address') this.value='';jQuery('#fp_searchAddressBtn').attr('disabled', false);">
                        <button class="btn " id="fp_searchAddressBtn" type="submit" disabled >Get Directions!</button>
                    </form>
                </div>
            <?php } ?>

			<?php if (!$this->item->params->get('hideintrotext')) { ?>
				<?php echo $this->item->description; ?>
			<?php } ?>
			<?php echo $this->item->fulldescription; ?>

			<?php if (count($this->item->customfields)) { ?>
				<div class="fp_customfields fp_content">
					<?php foreach ($this->item->customfields as $key=>$customfield) { ?>
						<?php $this->renderField($customfield); ?>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<div class="fp_right_column span4">
            <?php if ($this->item->address || $this->item->phone) { ?>
                <div class="row-fluid fp_address">
                    <?php if ($this->item->address) { ?>
                        <div class="span12">
                            <h3>Address:</h3>
                            <p><?php echo $this->item->address; ?></p>
                        </div>
                    <?php }?>
                    <?php if ($this->item->phone) { ?>
                        <div class="span6">
                            <h3>Phone:</h3>
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
		<a class="btn backtomap" href="<?php echo $this->item->backlink; ?>">Back to map</a>
	</p>
	<?php } ?>
	</div>
	<?php echo $this->loadTemplate('mapjs'); ?>
	<?php if (JFactory::getApplication()->input->get("debug")) {echo "<pre>"; print_r($this->item); echo"</pre>";} ?>

</div>


