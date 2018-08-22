<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 *
 * This file generates all the javascript required to show the map, markers and infoboxes.
 * In most custom templates this file should not require any changes and can be left as is.
 *
 * Backup this file before making any alterations.
 *
 * If you need to customise this file, create an override in your template and edit that.
 * Copy this file to templates/your+template/html/com_focalpoint/location/default_mapsjs.php
 *
 *
 *
 * To output a customfield use
 * 		$this->renderField($marker->customfields->yourcustomfield, $hidelabel, $buffer)
 *  	$hidelabel is TRUE or FALSE
 *      $buffer is TRUE or FALSE. If TRUE the output is buffered and returned. If FALSE it is output directly.
 *
 * To avoid notices first check that the field exists;
 *      if (!empty($marker->customfields->yourcustomfield)) { //Do something }
 *
 *
 * Alternatively iterate through the object $marker->customfields AS $field and call
 *  	$this->renderField($field,$hidelabel, $buffer);
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Load the Google API and initialise the map.
$document = JFactory::getDocument();
$document->addScript('//maps.googleapis.com/maps/api/js?key='.$this->item->params->get('apikey'));
$document->addScript(JURI::base().'components/com_focalpoint/assets/js/infobox.js');
$params             = JComponentHelper::getParams('com_focalpoint');
$showlisttab        = $this->item->params->get('locationlist');
$showmapsearch      = $this->item->params->get('mapsearchenabled');
$mapsearchzoom      = $this->item->params->get('mapsearchzoom');
$mapsearchrange     = $this->item->params->get('resultradius');
$mapsearchprompt    = $this->item->params->get('mapsearchprompt');
$searchassist       = ", ".$this->item->params->get('searchassist');
$fitbounds          = $this->item->params->get('fitbounds');
$markerclusters     = $this->item->params->get('markerclusters');
$listtabfirst       = $this->item->params->get('showlistfirst');
if($markerclusters) {
    $document->addScript(JURI::base().'plugins/focalpoint/markerclusters/assets/markerclusterer.js');
}
$script ='
	var allowScrollTo = false;
	var searchTxt = "";
	var showlisttab = '.$showlisttab.';
	var showmapsearch = '.$showmapsearch.';
	var mapsearchzoom = '.$mapsearchzoom.';
	var mapsearchrange = '.$mapsearchrange.';
	var mapsearchprompt = "'.$mapsearchprompt.'";
	var searchassist = "'.$searchassist.'";
	var fitbounds = '.($fitbounds?"1":"0").';
	var markerclusters = '.($markerclusters?"1":"0").';
	var listtabfirst = '.($listtabfirst?"1":"0").';
	var map;
	var mapCenter = new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.');
	var markerCluster;
    var clusterMarkers = [];
    if(typeof clusterStyles === \'undefined\') {
        var clusterStyles = [];
    }
    var marker= new Array();
	function updateActiveCount(marker){
		var locationTxt ="";
		var status=""
		var activeCount = 0;
		jQuery.each(marker, function(i,m){
			if(typeof m !== \'undefined\') {
				if (marker[i].status > 0 ) {
					activeCount += 1;
					status = status+" "+marker[i].status;
				}
			}
		});
		if (searchTxt !="" ) {
			locationTxt = " ('.JText::_("COM_FOCALPOINT_WITHIN").' "+mapsearchrange+"'.JText::_("COM_FOCALPOINT_DISTANCE").' "+searchTxt+")";
		}
		var locationPlural = "'.JText::_("COM_FOCALPOINT_LOCATIONS").'";
		if (activeCount == 1) { locationPlural = "'.JText::_("COM_FOCALPOINT_LOCATION").'"; }
		jQuery("#activecount").html("'.JText::_("COM_FOCALPOINT_SHOWING").' " + activeCount +" "+locationPlural+locationTxt+".");
		if (activeCount == 0){
			if (jQuery(".nolocations").length == 0){
				jQuery("#fp_locationlist .fp_ll_holder").append("<div class=\"nolocations\">'.JText::_("COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED").'</div>");
			}
		} else {
			jQuery(".nolocations").remove();
		}
	}
    function initialize() {
        var mapProp = {
            center:new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'),
            zoom:'.$this->item->params->get('zoom').',
            maxZoom:'.$this->item->params->get('maxzoom','null').',
            mapTypeControl: '.$this->item->params->get('mapTypeControl').',
            zoomControl: '.$this->item->params->get('zoomControl').',
            scrollwheel: '.$this->item->params->get('scrollwheel').',
            streetViewControl: '.$this->item->params->get('streetViewControl').',
            panControl: '.$this->item->params->get('panControl').',
            draggable: '.$this->item->params->get('draggable').',
            mapTypeId:google.maps.MapTypeId.'.$this->item->params->get('mapTypeId').',
            styles: '.$this->item->params->get('mapstyle',"[]").'
        };
        map = new google.maps.Map(document.getElementById("fp_googleMap"),mapProp);
        var markerSets = new Array();
        var markerInfoBox = new Array();
        var mappedMarkers = new Array();
        var mapinfobox = false;
        var columns = 4;
        var columnCount = 0;
';

// Cycle through each location creating a marker and infobox.
foreach ($this->item->markerdata as $marker) {
    //Assemble the infobox.
    $marker->infodescription = "";
    if ($marker->params->get('infoshowaddress') && $marker->address !="") $marker->infodescription .= "<p>".JText::_($marker->address)."</p>";
    if ($marker->params->get('infoshowphone') && $marker->phone !="") $marker->infodescription .= "<p>".JText::_($marker->phone)."</p>";
    if ($marker->params->get('infoshowintro') && $marker->description !="") $marker->infodescription .= "<p>".JText::_($marker->description)."</p>";

    // Example. If a custom fields was defined called "yourcustomfield" the following line would render
    // that field in the infobox and location list
    if (!empty($marker->customfields->yourcustomfield->data)) $marker->infodescription .= $this->renderField($marker->customfields->yourcustomfield, true, true);

    $boxtext ='<h4>'.addslashes($marker->title).'</h4><div class=\"infoboxcontent\">'.addslashes(str_replace("src=\"images","src=\"".JUri::base(true)."/images",(str_replace(array("\n", "\t", "\r"), '', $marker->infodescription))));
    if (isset($marker->link)) $boxtext.='<p class=\"infoboxlink\"><a title=\"'.addslashes($marker->title).'\" href=\"'.$marker->link.'\">'.JText::_('COM_FOCALPOINT_FIND_OUT_MORE').'</a></p>';

    $boxtext.='<div class=\"infopointer\"></div></div>';
    $script .= '
    	if (jQuery.inArray('.$marker->id.' ,mappedMarkers) == -1) {
			var myCenter'.$marker->id.'=new google.maps.LatLng('.$marker->latitude.','.$marker->longitude.');
			marker['.$marker->id.']=new google.maps.Marker({
				position:myCenter'.$marker->id.',
				icon: "'.$marker->marker.'"
			});
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
			google.maps.event.addListener(map, "click", function(e) {
                contextMenu:true
            });
            if (markerclusters) {
			    clusterMarkers.push(marker['.$marker->id.']);
			} else {
			    marker['.$marker->id.'].setMap(map);
			}
			google.maps.event.addListener(marker['.$marker->id.'], \'click\', function() {
				if (mapinfobox == markerInfoBox['.$marker->id.'] && mapinfobox.getVisible()) {
					mapinfobox.close();
				} else {
					if (mapinfobox) {
						mapinfobox.close()
					}
					mapinfobox = markerInfoBox['.$marker->id.'];
					mapinfobox.open(map,marker['.$marker->id.']);
				}
			});
			if (showlisttab) {
				jQuery("#fp_locationlist .fp_ll_holder").append("<div class=\"fp_list_marker'.$marker->id.' fp_listitem\">"+boxText'.$marker->id.'+"</div>");
			}
			marker['.$marker->id.'].status = 0;
			marker['.$marker->id.'].lat='.$marker->latitude.';
			marker['.$marker->id.'].lng= '.$marker->longitude.';
			jQuery(".fp_list_marker'.$marker->id.'").status = 0;
        }
		marker['.$marker->id.'].status += 1;
		jQuery(".fp_list_marker'.$marker->id.'").status +=1;

        if(typeof markerSets['.$marker->locationtype_id.'] === \'undefined\') {
            markerSets['.$marker->locationtype_id.'] = new Array();
        }
        mappedMarkers.push('.$marker->id.')
        markerSets['.$marker->locationtype_id.'].push('.$marker->id.');

    ';
}

// Close the initialize() function. Use JQuery for the click events on the sidebar links (a.markertoggles)
// and setup the load event.
$script .= '
        if (showlisttab) {
			jQuery("#locationlisttab").click(function(e){
				e.preventDefault();
				jQuery("a[href=\"#tabs1-map\"]").tab(\'show\');
				jQuery("#fp_googleMap").css("display","none");
				jQuery(".fp-map-view .nav-tabs li.active").removeClass("active");
				jQuery("#fp_locationlist_container").css("display","block");
				jQuery("#locationlisttab").parent().addClass("active");
				var locationListHeight = jQuery("#fp_locationlist .fp_ll_holder").outerHeight();
				jQuery("#fp_locationlist").css("height", locationListHeight);
			});
			jQuery(\'a[href="#tabs1-map"]\').click(function(){
				jQuery("#fp_googleMap").css("display","block");
				jQuery(".fp-map-view .nav-tabs li.active").addClass("active");
				jQuery("#fp_locationlist_container").css("display","none");
				jQuery("#locationlisttab").parent().removeClass("active");
			});
		}
        jQuery(".markertoggles").click(function() {
		    marker.forEach(function(m,i){
                markerInfoBox[i].close();
            });
            el = jQuery(this);
            mid = el.attr("data-marker-type");
            var arrlength = markerSets[mid].length;
            if (el.hasClass("active")) {
            	for (var i = 0; i < arrlength; i++) {
            		marker[markerSets[mid][i]].status -= 1;
                    if ( marker[markerSets[mid][i]].status == 0) {

                    	if (!markerclusters) {
                    	    marker[markerSets[mid][i]].setMap();
                    	}
                    	markerInfoBox[markerSets[mid][i]].close();
                    	jQuery(".fp_list_marker"+markerSets[mid][i]).fadeOut(100,function(){
							jQuery(this).addClass("fp_listitem_hidden");
							jQuery(this).appendTo("#fp_locationlist .fp_ll_holder");
                    	});

                	}
            	}
            	el.removeClass("active");
           	} else {
           		for (var i = 0; i < arrlength; i++) {
            		marker[markerSets[mid][i]].status += 1;
                    if ( marker[markerSets[mid][i]].status == 1) {

                        if (!markerclusters) {
                    	    marker[markerSets[mid][i]].setMap(map);
                    	}

                    	jQuery(".fp_list_marker"+markerSets[mid][i]).prependTo("#fp_locationlist .fp_ll_holder");
                    	jQuery(".fp_list_marker"+markerSets[mid][i]).fadeIn(100,function(){
							jQuery(this).removeClass("fp_listitem_hidden");
                    	});
                    }
            	}
                el.addClass("active");
            }
            if (fitbounds) {
                var bounds = new google.maps.LatLngBounds();
                var newbounds = false;
                marker.map(function(m){
                    if (m.status > 0) {
                        newbounds = true;
                        var thisbounds = new google.maps.LatLng(m.lat,m.lng);
                        bounds.extend(thisbounds);
                    }
                });
                if (newbounds) {
                    map.fitBounds(bounds);
                } else {
                    map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
                    map.setZoom('.$this->item->params->get('zoom').');
                }
            }
            if (markerclusters) {
                clusterMarkers = [];
                marker.forEach(function(m,i){
					if(marker[i].status > 0){
					    clusterMarkers.push(marker[i]);
					}
				});
                markerCluster.clearMarkers();
                markerCluster = new MarkerClusterer(map, clusterMarkers, {
                    styles: clusterStyles
                });
            }
			setTimeout(function(){
			var locationListHeight = jQuery("#fp_locationlist .fp_ll_holder").outerHeight();
				jQuery("#fp_locationlist").css("height", locationListHeight);
			},150);
			updateActiveCount(marker,searchTxt);
			if (allowScrollTo == true){
				//jQuery("html, body").animate({
				//	scrollTop: jQuery("#fp_main").offset().top
				//},300);
			}
            return false;
        });
        jQuery("ul.nav-tabs > li >a").click(function() {
            setTimeout(function(){
                google.maps.event.trigger(map, \'resize\');
                map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
                map.setZoom('.$this->item->params->get('zoom').');
            },500); 
        });
        jQuery(window).resize(function() {
            map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.')); 
        });
		jQuery("#fp_reset").click(function() {
			allowScrollTo = false;
			jQuery("#fp_searchAddress").val(mapsearchprompt);
			jQuery("#fp_searchAddressBtn").attr("disabled", true);
			searchTxt = "";
			marker.forEach(function(m,i){
				if (marker[i].status < -999 ){
					marker[i].status += 5000;
					marker[i].setMap(map);
					jQuery(".fp_list_marker"+i).fadeIn(100,function(){
						jQuery(this).removeClass("fp_listitem_hidden");
						jQuery(this).prependTo("#fp_locationlist .fp_ll_holder");
					});
				}
			});
			jQuery("#fp_toggle").each(function(){
				if ("on" == "'.$this->item->params->get('showmarkers').'") {
					jQuery(this).data("togglestate","off");
					jQuery(this).html("'. JText::_('COM_FOCALPOINT_BUTTTON_HIDE_ALL').'");
					jQuery(".markertoggles").each(function(e){
						if (jQuery(this).hasClass("active")) {
							jQuery(this).trigger("click");
						}
						jQuery(this).trigger("click");
					});
				} else {
					jQuery(this).data("togglestate","on");
					jQuery(this).html("'. JText::_('COM_FOCALPOINT_BUTTTON_SHOW_ALL').'");
					jQuery(".markertoggles").each(function(e){
						if (jQuery(this).hasClass("active")) {
							jQuery(this).trigger("click");
						}
					});
				}
			});
			allowScrollTo = true;
			map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
			map.setZoom('.$this->item->params->get('zoom').');
		});
		jQuery("#fp_toggle").click(function() {
			allowScrollTo = false;
			if (jQuery(this).data("togglestate") == "on") {
				jQuery(this).data("togglestate","off");
				jQuery(this).html("'. JText::_('COM_FOCALPOINT_BUTTTON_HIDE_ALL').'");
				jQuery(".markertoggles").each(function(e){
					if (!jQuery(this).hasClass("active")) {
						jQuery(this).trigger("click");
					}
				});
			} else {
				jQuery(this).data("togglestate","on");
				jQuery(this).html("'. JText::_('COM_FOCALPOINT_BUTTTON_SHOW_ALL').'");
				jQuery(".markertoggles").each(function(e){
					if (jQuery(this).hasClass("active")) {
						jQuery(this).trigger("click");
					}
				});
			}
			allowScrollTo = true;
		});
		if (showmapsearch) {
			var geocoder;
			var resultLat;
			var resultLng;
			jQuery("#fp_searchAddress").keypress(function(e){
                if (e.which == 13) {
                    return false;
                }
            });
			jQuery("#fp_searchAddressBtn").click(function() {
				geocoder = new google.maps.Geocoder();
				searchTxt = document.getElementById("fp_searchAddress").value;
				if (searchTxt == "") {return false;}
				geocoder.geocode( { "address": searchTxt+searchassist}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {

						resultLat = results[0].geometry.location.lat();
						resultLng = results[0].geometry.location.lng();
						allowScrollTo = false;
						marker.forEach(function(m,i){
							if (marker[i].status < -999 ){
								marker[i].status += 5000;
								if (!markerclusters){
								    marker[i].setMap(map);
								}
								jQuery(".fp_list_marker"+i).fadeIn(100,function(){
									jQuery(this).removeClass("fp_listitem_hidden");
									jQuery(this).prependTo("#fp_locationlist .fp_ll_holder");
								});
							}
						});
						jQuery("#fp_toggle").each(function(){
							jQuery(this).data("togglestate","off");
							jQuery(this).html("'. JText::_('COM_FOCALPOINT_BUTTTON_HIDE_ALL').'");
							jQuery(".markertoggles").each(function(e){
								if (jQuery(this).hasClass("active")) {
									jQuery(this).trigger("click");
								}
								jQuery(this).trigger("click");
							});
						});
						marker.forEach(function(m,i){
							var dLat = resultLat-m.lat;
							var dLong = resultLng-m.lng;
							var distance = Math.sqrt(dLat*dLat + dLong*dLong)*111.32;
							if (distance > mapsearchrange) {
								marker[i].status -= 5000;
                    			if ( marker[i].status < 1) {
                    				markerInfoBox[i].close();
                    				if (!markerclusters){
                    				    marker[i].setMap();
                    				}
                    				jQuery(".fp_list_marker"+i).fadeOut(100,function(){
										jQuery(this).addClass("fp_listitem_hidden");
										jQuery(this).appendTo("#fp_locationlist .fp_ll_holder");
                    				});
                    			}
							}
                    		updateActiveCount(marker,searchTxt);
							allowScrollTo = true;
						});
                        if (markerclusters) {
                            clusterMarkers = [];
                            marker.forEach(function(m,i){
                                if(marker[i].status > 0){
                                    clusterMarkers.push(marker[i]);
                                }
                            });

                            markerCluster.clearMarkers();
                            markerCluster = new MarkerClusterer(map, clusterMarkers, {
                                styles: clusterStyles
                            });
                        }
                        map.setCenter(results[0].geometry.location);
						map.setZoom(mapsearchzoom);
						setTimeout(function(){
							var locationListHeight = jQuery("#fp_locationlist .fp_ll_holder").outerHeight();
							jQuery("#fp_locationlist").css("height", locationListHeight);
						},500);
					} else {
						alert("Geocode was not successful for the following reason: " + status);
					}
				});
			});
		}
        jQuery("#fp_toggle").trigger("click");
        allowScrollTo = true;
        updateActiveCount(marker);

        if (markerclusters){
            markerCluster = new MarkerClusterer(map, clusterMarkers, {
                styles: clusterStyles
            });
        }

        if (showlisttab && (listtabfirst == 1)) {
            setTimeout(function(){
                jQuery("#locationlisttab").trigger("click");
            },100);
        }

        if ("off" == "'.$this->item->params->get('showmarkers').'") {
            setTimeout(function(){
                jQuery("#fp_toggle").trigger("click");
            },100);
        }
    }
    google.maps.event.addDomListener(window, \'load\', initialize);
';
$document->addScriptDeclaration($script);