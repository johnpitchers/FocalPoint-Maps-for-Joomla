<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 *
 */

// no direct access
defined('_JEXEC') or die;
$params = JComponentHelper::getParams('com_focalpoint');

// Location List tab parameters
$showlisttab = $this->item->params->get('locationlist');
$listtabfirst = $this->item->params->get('showlistfirst');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_focalpoint', JPATH_ADMINISTRATOR);

// Load the default CSS/JS files.
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_focalpoint/assets/css/focalpoint.css');

if ($this->item->params->get('loadBootstrap')) {
    $document->addStyleSheet(JURI::base() . 'components/com_focalpoint/assets/css/bootstrap.css');
    JHtml::_('bootstrap.framework');
}

// Load FocalPoint Plugins. Trigger onBeforeRenderMap
JPluginHelper::importPlugin('focalpoint');
$pluginTabs = JFactory::getApplication()->triggerEvent('onBeforeRenderMap', array(&$this->item));

// Set up width/height styles for the map and sidebar.
$containerStyle = "";
$mapStyle = "";
$sidebarStyle = "";
$mapsizexouter = "auto";
$mapsizey = "0";
$legendposition = $this->item->params->get('legendposition');
$sidebarClass = "fp_vert fp_" . $legendposition;
if ($this->item->params->get('mapsizecontrol') == 1) {
    $mapsizexouter = $this->item->params->get('mapsizex');
    $mapsizey = $this->item->params->get('mapsizey');
    $mapsizexouterfmt = str_replace(str_split(' 0123456789'), '', $mapsizexouter);
    $mapsizexouteramt = str_replace(str_split(' px%'), '', $mapsizexouter);
    $mapsizex = "auto";
    $mapStyle .= "min-height: " . $mapsizey . "; ";
    if ($legendposition == "left" || $legendposition == "right") {
        $sidebarx = str_replace(str_split(' px%'), '', $this->item->params->get('sidebarx'));
        $mapsizex = $mapsizexouteramt * (1 - ($sidebarx / 100)) . $mapsizexouterfmt;
        $mapStyle .= "width: " . $mapsizex . "; ";
        $sidebarStyle .= "width: " . $sidebarx . "%; ";
        $sidebarStyle .= "min-height: " . $mapsizey . "; ";
        $sidebarStyle .= "float: left; ";
        $sidebarClass = "fp_side fp_" . $legendposition;
    }
}
if (!empty($mapsizex)) {
    $containerStyle .= "width:" . $mapsizexouter . "; ";
}
if (!empty($mapsizey)) {
    $containerStyle .= "min-height:" . $mapsizey . "; ";
}

$pageclass_sfx = $this->item->params->get('pageclass_sfx');

?>

    <div id="focalpoint" class="fp-map-view <?php echo "legend_" . $legendposition . " " . $pageclass_sfx; ?>">

        <?php if (isset($this->item->page_title)) { ?>
            <h1><?php echo $this->item->page_title; ?></h1>
            <h2><?php echo $this->item->title; ?></h2>
        <?php } else { ?>
            <h1><?php echo $this->item->title; ?></h1>
        <?php } ?>

        <?php if ($this->item->text) { ?>
            <div class="fp_mapintro clearfix">
                <?php echo $this->item->text; ?>
            </div>
        <?php } ?>
        <div id="fp_main" class="clearfix">
            <?php if (count($this->item->tabs) || $showlisttab) { ?>
            <div id="tab-container" class="tab-container">
                <ul class='nav nav-tabs'>
                    <?php if ($showlisttab && $listtabfirst) { ?>
                        <li class=''><a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST')?></a></li>
                    <?php } ?>
                    <li class='active'><a href="#tabs1-map" data-toggle="tab"><?php echo JText::_('COM_FOCALPOINT_MAP')?></a></li>
                    <?php if ($showlisttab && !$listtabfirst) { ?>
                        <li class=''><a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST')?></a></li>
                    <?php } ?>
                    <?php if (count($this->item->tabs)) { ?>
                        <?php foreach ($this->item->tabs as $key => $tab) { ?>
                            <li><a href="#tabs1-<?php echo $key; ?>" data-toggle="tab"><?php echo $tab->name; ?></a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
                <?php } ?>

                <div class="tab-content">
                    <?php if (count($this->item->tabs) || $showlisttab) { ?>
                    <div id="tabs1-map" class="tab-pane active">
                        <?php } ?>
                        <div id="fp_googleMapContainer" class="<?php echo $sidebarClass; ?>"
                             style="<?php echo $containerStyle; ?>">
                            <?php if ($legendposition == "above" || $legendposition == "left") { ?>
                                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                                    <?php echo $this->loadTemplate('legend_' . $legendposition); ?>
                                </div>
                            <?php } ?>
                            <div id="fp_googleMap" style="<?php echo $mapStyle; ?>"></div>
                            <?php if ($showlisttab) { ?>
                                <div id="fp_locationlist_container">
                                    <div id="fp_locationlist" class="" style="<?php echo $mapStyle; ?>">
                                        <div class="fp_ll_holder"></div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($legendposition == "below" || $legendposition == "right") { ?>
                                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                                    <?php echo $this->loadTemplate('legend_' . $legendposition); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (count($this->item->tabs) || $showlisttab) { ?>
                    </div>
                <?php if (count($this->item->tabs)) { ?>
                    <?php foreach ($this->item->tabs as $key => $tab) { ?>
                        <div id="tabs1-<?php echo $key; ?>" class="fp-custom-tab tab-pane">
                            <?php echo $tab->content; ?>
                        </div>
                    <?php } ?>
                <?php } ?>
                <?php } ?>
                </div>
            </div>
            <?php if (JFactory::getApplication()->input->get("debug")) {
                echo "<textarea style='width:100%;height:500px;'><pre>";
                print_r($this->item);
                echo "</pre></textarea>";
            } ?>
        </div>
    </div>

<?php
//echo $this->loadTemplate('mapjs');
echo $this->loadTemplate('mapjs_uncommented');

// Load FocalPoint Plugins. Trigger onAfterRenderMap
JPluginHelper::importPlugin('focalpoint');
$pluginTabs = JFactory::getApplication()->triggerEvent('onAfterRenderMap', array(&$this->item));