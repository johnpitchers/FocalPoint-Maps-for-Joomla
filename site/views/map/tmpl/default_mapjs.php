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
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Load the Google API and initialise the map.
$document = JFactory::getDocument();
$document->addScript('//maps.googleapis.com/maps/api/js?key='.$this->item->params->get('apikey').'&sensor=false');
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
	var map;
	var markerCluster;
    var clusterMarkers = [];
    var marker= new Array();
	function updateActiveCount(marker){
		//Update active count.
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

		jQuery("#activecount").html("Showing " + activeCount +" "+locationPlural+locationTxt+".");

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

	$boxtext ='<h4>'.$marker->title.'</h4><div class=\"infoboxcontent\">'.addslashes(str_replace("src=\"images","src=\"".JUri::base(true)."/images",(str_replace(array("\n", "\t", "\r"), '', $marker->infodescription))));
	if (isset($marker->link)) $boxtext.='<p class=\"infoboxlink\"><a title=\"'.$marker->title.'\" href=\"'.$marker->link.'\">Find out more</a></p>';

	$boxtext.='<div class=\"infopointer\"></div></div>';
    $script .= '
    	//Check the marker hasnt been drawn already.
    	if (jQuery.inArray('.$marker->id.' ,mappedMarkers) == -1) {
			// Add marker for '.$marker->title.'
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

			//Populate the location list
			if (showlisttab) {
				jQuery("#fp_locationlist .fp_ll_holder").append("<div class=\"fp_list_marker'.$marker->id.' fp_listitem\">"+boxText'.$marker->id.'+"</div>");
			}

			// Status is used by search and filter functions
			marker['.$marker->id.'].status = 0;
			// Lat/lng is stored for easy access by search function
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

				//Adjust the height of the location list
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

		//Click funtion for legend toggles
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

            //Fitbounds (auto zoom and pan only if plugin is active.)
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

            // Redraw the clusters
            if (markerclusters) {
                clusterMarkers = [];
                marker.forEach(function(m,i){
				    //console.log(marker[i].status);
					if(marker[i].status > 0){
					    clusterMarkers.push(marker[i]);
					}
				});

                markerCluster.clearMarkers();
                markerCluster = new MarkerClusterer(map, clusterMarkers);
            }

			//Adjust the height of the location list to fit the locations
			setTimeout(function(){
			var locationListHeight = jQuery("#fp_locationlist .fp_ll_holder").outerHeight();
				jQuery("#fp_locationlist").css("height", locationListHeight);
			},150);

			updateActiveCount(marker,searchTxt);

            // The following scrolls to the map when a marker is toggled. Can be helpful on mobiles or when large
            // legends are used. But can also get annoying. Uncomment if you want to use it.
			if (allowScrollTo == true){
				//jQuery("html, body").animate({
				//	scrollTop: jQuery("#fp_main").offset().top
				//},300);
			}
            return false;
        });

        //Returns the map to the centre position when changing tabs.
        jQuery("ul.nav-tabs > li >a").click(function() {
            setTimeout(function(){
                google.maps.event.trigger(map, \'resize\');
                map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
                map.setZoom('.$this->item->params->get('zoom').');
            },500); 
        });

        //Centres the map when the window is resized or orientation is changed. By default Google Maps pins to the top left corner
        jQuery(window).resize(function() {
            map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.')); 
        });

		//Ability to reset the map and markers to their original state.
		jQuery("#fp_reset").click(function() {
			allowScrollTo = false;
			//Reset the search box;
			jQuery("#fp_searchAddress").val(mapsearchprompt);
			jQuery("#fp_searchAddressBtn").attr("disabled", true);
			searchTxt = "";

			//If a search has been applied and markers have been filtered out we need to restore them.
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
			allowScrollTo = true;
			map.panTo(new google.maps.LatLng('.$this->item->latitude.','.$this->item->longitude.'));
			map.setZoom('.$this->item->params->get('zoom').');
		});

		// Show/Hide markers toggle functionality.
		jQuery("#fp_toggle").click(function() {

			allowScrollTo = false;
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
			allowScrollTo = true;
		});

		if (showmapsearch) {
			// Suburb/Location Search.
			var geocoder;
			var resultLat;
			var resultLng;
			//Disable the enter key
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

						//If a search has been applied already and markers have been filtered out we need to restore them.
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

						// The map is centred and zoomed to the correct level. Now turn on the markers so we can
						// see what is there.
						jQuery("#fp_toggle").each(function(){
							jQuery(this).data("togglestate","off");
							jQuery(this).html("Hide all");
							jQuery(".markertoggles").each(function(e){
								if (jQuery(this).hasClass("active")) {
									jQuery(this).trigger("click");
								}
								jQuery(this).trigger("click");
							});
						});

						//Filter out results not in the search radius;
						marker.forEach(function(m,i){
							//console.log(marker[i].status);
							var dLat = resultLat-m.lat;
							var dLong = resultLng-m.lng;
							var distance = Math.sqrt(dLat*dLat + dLong*dLong)*111.32; // Very rough. Doesnt account for curvature of earth or reduction of distance between meridians closer to the poles
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

						// Redraw the clusters
                        if (markerclusters) {
                            clusterMarkers = [];
                            marker.forEach(function(m,i){
                                if(marker[i].status > 0){
                                    clusterMarkers.push(marker[i]);
                                }
                            });

                            markerCluster.clearMarkers();
                            markerCluster = new MarkerClusterer(map, clusterMarkers);
                        }
                        map.setCenter(results[0].geometry.location);
						map.setZoom(mapsearchzoom);
						//Adjust the height of the location list to fit the locations
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
            markerCluster = new MarkerClusterer(map, clusterMarkers);
        }

    }


    google.maps.event.addDomListener(window, \'load\', initialize);

';
$document->addScriptDeclaration($script);