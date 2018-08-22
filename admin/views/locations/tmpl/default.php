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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$task = JFactory::getApplication()->input->get('task');
if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_focalpoint&task=locations.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'locationsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>

    <form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=locations'); ?>" method="post"
          name="adminForm" id="adminForm" class="tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?>">
        <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif; ?>
                <?php
                // Search tools bar
                if ($task != "congratulations") echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                ?>
                <?php if (empty($this->items)) : ?>
                    <?php if ($task == "congratulations") { ?>

                        <div class="hero-unit" style="text-align:left;">
                            <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LOCATIONS_NEW'); ?>

                        </div>

                    <?php } else { ?>
                        <div class="alert alert-no-items">
                            <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                        </div>
                    <?php } ?>
                <?php else : ?>
                    <table class="table table-striped" id="locationsList">
                        <thead>
                        <tr>
                            <th width="1%" class="nowrap center hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                            </th>
                            <th width="1%" class="hidden-phone">
                                <?php echo JHtml::_('grid.checkall'); ?>
                            </th>
                            <th width="1%" style="min-width:55px" class="nowrap center">
                                <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>
                            <th>
                                <?php echo JHtml::_('searchtools.sort', 'COM_FOCALPOINT_LOCATIONS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                            </th>

                            <th>
                                <?php echo JHtml::_('searchtools.sort', 'COM_FOCALPOINT_LOCATIONS_MAP_ID', 'map_title', $listDirn, $listOrder); ?>
                            </th>
                            <th>
                                <?php echo JHtml::_('searchtools.sort', 'COM_FOCALPOINT_LOCATIONS_TYPE', 'locationtype_title', $listDirn, $listOrder); ?>
                            </th>


                            <th width="10%" class="nowrap hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', 'COM_FOCALPOINT_LOCATIONS_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
                            </th>
                            <th width="1%" class="nowrap hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>

                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            $ordering = ($listOrder == 'a.ordering');
                            $canCreate = $user->authorise('core.create', 'com_focalpoint');
                            $canEdit = $user->authorise('core.edit', 'com_focalpoint');
                            $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                            $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->map_id; ?>">
                                <td class="order nowrap center hidden-phone">
                                    <?php
                                    $iconClass = '';
                                    if (!$canChange) {
                                        $iconClass = ' inactive';
                                    } elseif (!$saveOrder) {
                                        $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                                    }
                                    ?>
                                    <span class="sortable-handler<?php echo $iconClass ?>">
										<i class="icon-menu"></i>
									</span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" style="display:none" name="order[]" size="5"
                                               value="<?php echo $item->ordering; ?>" class=""/>
                                    <?php endif; ?>
                                </td>
                                <td class="center hidden-phone">
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="center">
                                    <div class="btn-group">
                                        <?php echo JHtml::_('jgrid.published', $item->state, $i, 'locations.', $canChange, 'cb'); ?>
                                    </div>
                                </td>

                                <td class="has-context">
                                    <div class="pull-left">
                                        <?php if ($item->checked_out) : ?>
                                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'locations.', $canCheckin); ?>
                                        <?php endif; ?>
                                        <?php if ($canEdit) : ?>
                                            <a href="<?php echo JRoute::_('index.php?option=com_focalpoint&task=location.edit&id=' . $item->id); ?>"
                                               title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                                <?php echo $this->escape($item->title); ?></a>
                                        <?php else : ?>
                                            <?php echo $this->escape($item->title); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="">
                                    <?php echo $item->map_title; ?>
                                </td>

                                <td class="">
                                    <?php echo $item->locationtype_title; ?>
                                </td>

                                <td class="small hidden-phone">
                                    <?php echo $item->created_by; ?>
                                </td>


                                <td class="center hidden-phone">
                                    <?php echo (int)$item->id; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>
                <div>
                    <input type="hidden" name="task" value=""/>
                    <input type="hidden" name="boxchecked" value="0"/>
                    <?php echo JHtml::_('form.token'); ?>
                </div>
            </div>
    </form>
<?php //echo"<pre>"; print_r($this->items); echo "</pre>"; echo"<pre>"; print_r($this->state); echo "</pre>";?>