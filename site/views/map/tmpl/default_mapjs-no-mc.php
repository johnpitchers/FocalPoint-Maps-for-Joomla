<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */
// *********************************************************
// 
// This file generates all the javascript required to show the map, markers and infoboxes.
// In most custom templates this file should not require any changes and can be left as is.
// 
// Backup this file before making any alterations.
// 
// *********************************************************


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Load the Google API and initialise the map.

$document = JFactory::getDocument();
$document->addScript('http://maps.googleapis.com/maps/api/js?key='.$this->item->params->get('apikey').'&sensor=false'); 
$document->addScript(JURI::base().'components/com_focalpoint/assets/js/infobox.js'); 

$script ='
    function initialize() {
        var mapProp = {
            center:new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'),
            zoom:'.$this->item->params->get('zoom').',
            mapTypeControl: '.$this->item->params->get('mapTypeControl').',
            zoomControl: '.$this->item->params->get('zoomControl').',
            scrollwheel: '.$this->item->params->get('scrollwheel').',
            streetViewControl: '.$this->item->params->get('streetViewControl').',
            panControl: '.$this->item->params->get('panControl').',
            draggable: '.$this->item->params->get('draggable').',
            mapTypeId:google.maps.MapTypeId.'.$this->item->params->get('mapTypeId').'
        };
        var map=new google.maps.Map(document.getElementById("fp_googleMap"),mapProp);
        var markerSets = new Array();
        var marker= new Array();    
        var markerInfoBox = new Array();
        var mapinfobox;
';

// Cycle through each location creating a marker and infobox.
foreach ($this->item->markerdata as $marker) {
	//Assemble the infobox.
	$marker->infodescription = "";
	if ($marker->params->get('infoshowaddress') && $marker->address !="") $marker->infodescription .= "<p>".JText::_($marker->address)."</p>";
	if ($marker->params->get('infoshowphone') && $marker->phone !="") $marker->infodescription .= "<p>".JText::_($marker->phone)."</p>";
	if ($marker->params->get('infoshowintro') && $marker->description !="") $marker->infodescription .= "<p>".JText::_($marker->description)."</p>";

	$boxtext ='<h4>'.$marker->title.'</h4><div class=\"infoboxcontent\">'.addslashes(str_replace(array("\n", "\t", "\r"), '', $marker->infodescription));
    if ($marker->link) $boxtext.='<p class=\"infoboxlink\"><a title=\"'.$marker->title.'\" href=\"'.$marker->link.'\">Find out more</a></p>';
    $boxtext.='<div class=\"infopointer\"></div></div>';
    $script .= '
        // Add marker for '.$marker->title.'
        var myCenter'.$marker->id.'=new google.maps.LatLng('.$marker->latitude.','.$marker->longitude.');
        marker['.$marker->id.']=new google.maps.Marker({
            position:myCenter'.$marker->id.',
            icon: "'.$marker->marker.'"
        });
        //marker['.$marker->id.'].setMap(map);
        //marker['.$marker->id.'].status = 1;    
        var boxText'.$marker->id.' = "'.$boxtext.'";
        markerInfoBox['.$marker->id.'] = new InfoBox({
            content: boxText'.$marker->id.',
            alignBottom: true, 
            position: new google.maps.LatLng('.$marker->latitude.','.$marker->longitude.'),
            pixelOffset: new google.maps.Size(-160, -55),
            maxWidth: 320,
            zIndex: null,
            closeBoxMargin: "7px 5px 1px 1px",
            closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
            infoBoxClearance: new google.maps.Size(20, 30)
        });
        
        google.maps.event.addListener(marker['.$marker->id.'], \'click\', function() {
            if (mapinfobox) {
                mapinfobox.close()
            }
            mapinfobox = markerInfoBox['.$marker->id.'];
            mapinfobox.open(map,marker['.$marker->id.']);
        });
        
        if(typeof markerSets['.$marker->type.'] === \'undefined\') {
            markerSets['.$marker->type.'] = new Array();
        }
        
        markerSets['.$marker->type.'].push('.$marker->id.');
    ';
}

// Close the initialize() function. Use JQuery for the click events on the sidebar links (a.markertoggles)
// and setup the load event.
$script .= '        
        jQuery.noConflict();
        jQuery(".markertoggles").click(function() {
            el = jQuery(this);
            mid = el.attr("data-marker-type");

            var arrlength = markerSets[mid].length;
            for (var i = 0; i < arrlength; i++) { 
                if ( marker[markerSets[mid][i]].status == 1) {
                    marker[markerSets[mid][i]].setMap(); 
                    marker[markerSets[mid][i]].status = 0;
                    markerInfoBox[markerSets[mid][i]].close();
                    el.removeClass("active");
                } else {
                    marker[markerSets[mid][i]].setMap(map); 
                    marker[markerSets[mid][i]].status = 1;
                    el.addClass("active");
                }
            }
            return false;
        });

        //Returns the map to the centre position when changing tabs.
        jQuery("ul.nav-tabs > li >a").click(function() {
            setTimeout(function(){
                google.maps.event.trigger(map, \'resize\');
                map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
            },500); 
        });

        //Centres the map when the window is resized or orientation is changed. By default Google Maps pins to the top left corner
        jQuery(window).resize(function() {
            map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.')); 
        });

		//Ability to reset the map and markers to their original state.
		jQuery("#fp_reset").click(function() {
			map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
			map.setZoom('.$this->item->params->get('zoom').');
			jQuery("#fp_toggle").each(function(){
				if ("on" == "'.$this->item->params->get('showmarkers').'") {
					jQuery(this).data("togglestate","off");
					jQuery(this).html("Hide all");
					jQuery(".markertoggles").each(function(e){
						if (jQuery(this).hasClass("active")) {
							jQuery(this).trigger("click");
						}
						jQuery(this).trigger("click");
					});
				} else {
					jQuery(this).data("togglestate","on");
					jQuery(this).html("Show all");
					jQuery(".markertoggles").each(function(e){
						if (jQuery(this).hasClass("active")) {
							jQuery(this).trigger("click");
						}
					});
				}
			});
		});

		// Show/Hide markers toggle functionality.
		jQuery("#fp_toggle").click(function() {
			if (jQuery(this).data("togglestate") == "on") {
				jQuery(this).data("togglestate","off");
				jQuery(this).html("Hide all");
				jQuery(".markertoggles").each(function(e){
					if (!jQuery(this).hasClass("active")) {
						jQuery(this).trigger("click");
					}
				});
			} else {
				jQuery(this).data("togglestate","on");
				jQuery(this).html("Show all");
				jQuery(".markertoggles").each(function(e){
					if (jQuery(this).hasClass("active")) {
						jQuery(this).trigger("click");
					}
				});
			}
		});
        jQuery("#fp_toggle").trigger("click");

		';

    $script .='
    }       
    google.maps.event.addDomListener(window, \'load\', initialize);
';



$document->addScriptDeclaration($script);