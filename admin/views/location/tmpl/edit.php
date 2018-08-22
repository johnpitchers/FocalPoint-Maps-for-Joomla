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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$paramsFieldSets = $this->form->getFieldsets('params');
$metaFieldSets = $this->form->getFieldsets('metadata');

// Import CSS + JS
$document = JFactory::getDocument();
$params = JComponentHelper::getParams('com_focalpoint');
$document->addStyleSheet('components/com_focalpoint/assets/css/focalpoint.css');
$document->addScript('//maps.googleapis.com/maps/api/js?key='.$params->get('apikey'));

//Check Multicategorisation plugin?
//$multicategorisation = false;
//if ($plugin = JPluginHelper::getPlugin('focalpoint','multicategorisation')){
//    $multicategorisation = true;
//}

$multicategorisation = true;

?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'location.cancel' || document.formvalidator.isValid(document.id('location-form'))) {
            Joomla.submitform(task, document.getElementById('location-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm" id="location-form"
      class="tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?> form-validate">
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>

    <?php echo JHtml::_('bootstrap.startTabSet', 'location', array('active' => 'basic')); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'location', 'basic', "Basic"); ?>
    <div class="row-fluid">
        <div class="span9">
            <div class="form-vertical">
                <?php echo $this->getForm()->getControlGroup('image'); ?>
                <?php echo $this->getForm()->getControlGroup('description'); ?>

                <?php //Need to review the following code. Carried from original J25 build. May not be required.
                foreach ((array)$this->item->type as $value):
                    if (!is_array($value)):
                        echo '<input type="hidden" class="type" name="jform[typehidden][' . $value . ']" value="' . $value . '" />';
                    endif;
                endforeach;
                ?>
                <script type="text/javascript">
                    jQuery.noConflict();
                    jQuery('input:hidden.type').each(function () {
                        var name = jQuery(this).attr('name');
                        if (name.indexOf('typehidden')) {
                            jQuery('#jform_type option[value="' + jQuery(this).val() + '"]').attr('selected', true);
                        }
                    });
                </script>
                <?php // End review ?>
            </div>
        </div>
        <div class="span3">
            <div class="form-vertical">
                <?php echo $this->getForm()->getControlGroup('map_id'); ?>
                <?php echo $this->getForm()->getControlGroup('type'); ?>
                <?php if (!$multicategorisation) { ?>
                <div style="display:none;">
                    <?php } ?>
                    <?php echo $this->getForm()->getControlGroup('othertypes'); ?>
                    <?php if (!$multicategorisation) { ?>
                </div>
                <?php } ?>
                <?php echo $this->getForm()->getControlGroup('address'); ?>
                <?php echo $this->getForm()->getControlGroup('phone'); ?>
                <?php //echo $this->getForm()->getControlGroup('geoaddress'); ?>
                <!-- Button to trigger modal -->
                <a id="openGeocoder" href="#myModal" role="button" class="btn btn-mini btn-primary" data-toggle="modal"><span
                        class="icon-out-2 small"></span> Open GeoCoder Tool</a>
                <?php echo $this->getForm()->getControlGroup('latitude'); ?>
                <?php echo $this->getForm()->getControlGroup('longitude'); ?>
                <?php echo $this->getForm()->getControlGroup('marker'); ?>
                <?php //echo $this->getForm()->getControlGroup('keylocation'); ?>
                <?php echo $this->form->getInput('created_by'); ?>
            </div>
        </div>
    </div>

    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'location', 'customfields', JText::_('COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS')); ?>
    <div class="row-fluid">
        <div class="span7">
            <div class="form-horizontal">
                <input type="hidden" name="jform[customfieldsdata]" value=""/>
                <?php echo $this->item->customformfieldshtml; ?>
            </div>
        </div>
    </div>
    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'location', 'linkoptions', JText::_('COM_FOCALPOINT_LEGEND_LINK_OPTIONS')); ?>
    <div class="form-horizontal">
        <?php echo $this->getForm()->getControlGroup('showaddress'); ?>
        <?php echo $this->getForm()->getControlGroup('showintro'); ?>
        <?php echo $this->getForm()->getControlGroup('linktype'); ?>
        <?php echo $this->getForm()->getControlGroup('altlink'); ?>
        <?php echo $this->getForm()->getControlGroup('maplinkid'); ?>
        <?php echo $this->getForm()->getControlGroup('menulink'); ?>
    </div>

    <?php echo JHtml::_('bootstrap.endTab'); ?>

    <?php foreach ($metaFieldSets as $name => $fieldSet) : ?>
        <?php echo JHtml::_('bootstrap.addTab', 'location', $name . '-params', JText::_($fieldSet->label)); ?>
        <div class="form-horizontal">
            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php echo $field->input; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        </div>
    <?php endforeach; ?>

    <?php foreach ($paramsFieldSets as $name => $fieldSet) : ?>
        <?php echo JHtml::_('bootstrap.addTab', 'location', $name . '-params', JText::_($fieldSet->label)); ?>
        <div class="form-horizontal">
            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php echo $field->input; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php endforeach; ?>

    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>

</form>

<!-- Modal -->
<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Drag the marker or enter a location</h3>
    </div>
    <div class="modal-body">
        <div class="row-fluid">
            <div id="mapCanvas"></div>
        </div>
        <div class="row-fluid">
            <div class="input-append span12">
                <input class="span6" id="geoaddress" type="text" value="Enter an address..."
                       onblur="if (this.value=='') {this.value='Enter an address...';jQuery('#fp_searchAddressBtn').attr('disabled', true);}"
                       onfocus="if (this.value=='Enter an address...') this.value='';jQuery('#fp_searchAddressBtn').attr('disabled', false);">
                <input type="button" id="fp_searchAddressBtn" value="GeoCode this!" disabled class="btn"
                       onclick="codeAddress()">
            </div>
        </div>
        <div class="row-fluid">
            <b>Current position:</b>

            <div id="info"></div>
        </div>

    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id="saveLatLng" class="btn btn-primary" data-dismiss="modal">Save Lat/Lng</button>
    </div>
</div>

<script>
    //Google Maps API V3 functions for Geocoding Map Centre point.
    var geocoder;
    var map;
    var marker;
    var latLng;
    var zoom = 15;

    function updateMarkerPosition(latLng) {
        document.getElementById('info').innerHTML = [
            latLng.lat(),
            latLng.lng()
        ].join(', ');
    }
    function initialise() {
        geocoder = new google.maps.Geocoder();
        var startLat = jQuery("#jform_latitude").val();
        var startLng = jQuery("#jform_longitude").val();
        if (startLat == "") {
            startLat = -31.9530044;
            zoom = 2
        }
        if (startLng == "") {
            startLng = 115.8574693;
            zoom = 2
        }
        latLng = new google.maps.LatLng(startLat, startLng);
        map = new google.maps.Map(document.getElementById('mapCanvas'), {
            zoom: zoom,
            center: latLng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        marker = new google.maps.Marker({
            position: latLng,
            title: 'Point A',
            map: map,
            draggable: true
        });

        // Update current position info.
        updateMarkerPosition(latLng);

        google.maps.event.addListener(marker, 'drag', function () {
            updateMarkerPosition(marker.getPosition());
        });
    }

    function codeAddress() {
        var geoaddress = document.getElementById("geoaddress").value;
        geocoder.geocode({ 'address': geoaddress}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                marker.setPosition(results[0].geometry.location);
                map.setZoom(15);
                updateMarkerPosition(marker.getPosition());
            } else {
                alert("Geocode was not successful for the following reason: " + status);
            }
        });
    }

    jQuery("#openGeocoder").click(function () {
        setTimeout(function () {
            google.maps.event.trigger(map, 'resize');
            map.panTo(marker.getPosition());
        }, 800);
    });

    jQuery("#saveLatLng").click(function () {
        jQuery("#jform_latitude").val(marker.getPosition().lat());
        jQuery("#jform_longitude").val(marker.getPosition().lng());
    });

    // Onload handler to fire off the app.
    google.maps.event.addDomListener(window, 'load', initialise);
</script>