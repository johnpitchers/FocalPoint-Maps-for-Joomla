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

    $data = $this->item->markerdata;
    $ulclass    = "";
    $liclass    = "";
    $html       = "";
    $first      = true;

    foreach ($data as $item) {
        if ($item->legendalias != $ulclass) {
            $ulclass = $item->legendalias;
            if (!$first) {
                $html .="</ul></div>";
            }
            $html .= '<div class="'.$ulclass.'"><h4>'.$item->legend."<small>".$item->legendsubtitle."</small></h4>";
            $html .= '<ul class="sidebar '.$ulclass.'">';
            $first = false;   
        }
        if ($liclass != $item->locationtypealias) {
			$html .= "<li><a data-marker-type='".$item->locationtype_id."' class='active markertoggles markers-".$item->locationtypealias."' href='#'>".$item->locationtype."</a></li>";
			$liclass = $item->locationtypealias;
        }
    }
    $html .="</ul></div>";
	$html .= $this->loadTemplate('legend_buttons');
    echo $html;