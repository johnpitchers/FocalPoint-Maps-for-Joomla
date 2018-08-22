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
JHtml::_('jquery.ui', array('core', 'sortable'));

$paramsFieldSets = $this->form->getFieldsets('params');
$metaFieldSets = $this->form->getFieldsets('metadata');

// Import CSS + JS
$document = JFactory::getDocument();

$params = JComponentHelper::getParams('com_focalpoint');
$document->addStyleSheet('components/com_focalpoint/assets/css/focalpoint.css');
$document->addScript('//maps.googleapis.com/maps/api/js?key='.$params->get('apikey'));
?>

<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'map.cancel' || document.formvalidator.isValid(document.id('map-form'))) {
            Joomla.submitform(task, document.getElementById('map-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm" id="map-form"
      class="tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?> form-validate">
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>

    <?php echo JHtml::_('bootstrap.startTabSet', 'map', array('active' => 'basic')); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'map', 'basic', "Basic Settings"); ?>
    <div class="form-vertical">
        <div class="row-fluid">
            <div class="span9">
                <?php echo $this->getForm()->getControlGroup('text'); ?>
            </div>
            <div class="span3">
                <?php echo $this->getForm()->getControlGroup('state'); ?>
                <?php echo $this->getForm()->getControlGroup('centerpoint'); ?>
                <!-- Button to trigger modal -->
                <a id="openGeocoder" href="#myModal" role="button" class="btn btn-mini btn-primary" data-toggle="modal"><span
                        class="icon-out-2 small"></span> Open GeoCoder Tool</a>
                <?php echo $this->getForm()->getControlGroup('latitude'); ?>
                <?php echo $this->getForm()->getControlGroup('longitude'); ?>
                <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getInput('created_by'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'map', 'tabs', "Tabs"); ?>

    <div class="form-vertical">
        <div class="row-fluid">
            <div class="span7 customfields">
                <?php
                $inserthtml = "";
                $config_editor = JFactory::getConfig()->get('editor');
                if (!empty($this->item->tabs)) {
                    //echo "<pre>";
                    //print_r( $this->item->tabsdata );
                    //echo "</pre>";
                    foreach ($this->item->tabs as $key => $tab) {

                        $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Tab</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input type="text" class="field" name="jform[tab][' . $key . '][name]" value="' . $tab->name . '" /></div></div>';
                        $editor[$key] = JEditor::getInstance($config_editor);
                        $inserthtml .= '<div class="control-group"><div class="controls">' . $editor[$key]->display('jform[tab][' . $key . '][content]', $tab->content, '100%', NULL, NULL, NULL, TRUE) . '</div></div>';
                        $inserthtml .= '</fieldset>';
                        $customFieldId = $key;
                    }
                    $inserthtml .= "<script>jQuery.noConflict();";
                    $inserthtml .= "jQuery('.deletefield').click(function(){";
                    $inserthtml .= "    if (confirm('Delete this field?')) {";
                    $inserthtml .= "        jQuery(this).tooltip('hide');";
                    $inserthtml .= "        jQuery(this).parent().remove();";
                    $inserthtml .= "    }";
                    $inserthtml .= "});";
                    $inserthtml .= "</script>";
                }

                $inserthtml .= "<script>";
                $inserthtml .= "    jQuery('.customfields').sortable({handle : 'legend',axis:'y',opacity:'0.6', distance:'1'});";
                $inserthtml .= "</script>";
                echo $inserthtml;
                ?>
            </div>
        </div>
        <h4><?php echo JText::_('COM_FOCALPOINT_FORM_MAP_TABS_DESCRIPTION'); ?></h4>
        <dl class="adminformlist">
            <dd><a id="add-tab" id="add-tab" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Add
                    new tab</a></dd>
        </dl>
    </div>
    <?php echo JHtml::_('bootstrap.endTab'); ?>

    <?php
    // Load FocalPoint Plugins. Trigger onLoadMapTabs
    JPluginHelper::importPlugin('focalpoint');
    $pluginTabs = JFactory::getApplication()->triggerEvent('onLoadMapTabs', array(&$this->item));
    ?>

    <?php foreach ($metaFieldSets as $name => $fieldSet) : ?>
        <?php echo JHtml::_('bootstrap.addTab', 'map', $name . '-params', JText::_($fieldSet->label)); ?>
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
        <?php echo JHtml::_('bootstrap.addTab', 'map', $name . '-params', JText::_($fieldSet->label)); ?>
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

    function makeid() {
        var text = "";
        var possible = "abcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < 10; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    jQuery.noConflict();
    jQuery(document).ready(function () {

        jQuery('#add-tab').click(function () {
            var id = makeid();

            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;New Tab</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input type="text" class="field" name="jform[tab][' + id + '][name]" value="" /></li><input type="hidden" name="jform[tab][' + id + '][content]" value="" />';
            inserthtml = inserthtml + '<p>Save this configuration to make this tab editable.</p>';
            inserthtml = inserthtml + '<div></div>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.deletefield').click(function () {
                //alert("delete");
                jQuery(this).tooltip('hide');
                jQuery(this).parent().remove();
            });
            return false;
        });
    });

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

        // Add dragging event listeners.
        google.maps.event.addListener(marker, 'dragstart', function () {
        });

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