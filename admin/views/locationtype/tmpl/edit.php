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

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_focalpoint/assets/css/focalpoint.css');
?>

<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'locationtype.cancel' || document.formvalidator.isValid(document.id('locationtype-form'))) {
            Joomla.submitform(task, document.getElementById('locationtype-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>


<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm" id="locationtype-form"
      class="tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?> form-validate">
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>

    <?php echo JHtml::_('bootstrap.startTabSet', 'locationtype', array('active' => 'general')); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'locationtype', 'general', "General"); ?>
    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span12">
                <?php echo $this->getForm()->getControlGroup('state'); ?>
                <?php echo $this->getForm()->getControlGroup('legend'); ?>
                <?php echo $this->getForm()->getControlGroup('marker'); ?>
                <input type="hidden" name="jform[customfields]" value="<?php echo $this->item->customfields; ?>"/>
                <?php echo $this->form->getInput('created_by'); ?>
            </div>
        </div>
    </div>
    <?php echo JHtml::_('bootstrap.endTab'); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'locationtype', 'customfields', JText::_('COM_FOCALPOINT_LOCATIONTYPE_CUSTOM_FIELDS_LABEL')); ?>
    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span7 customfields">
                <?php
                $inserthtml = "";
                if (isset($this->item->custom)) {
                    //echo "<pre>";
                    //print_r( $this->item->custom );
                    //echo "</pre>";
                    foreach ($this->item->custom as $key1 => $array) {
                        $thiskey = explode(".", $key1);
                        switch ($thiskey[0]) {
                            case "textbox":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Textbox</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "textarea":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Textarea</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Load Editor</label></div><div class="controls"><select name="jform[custom][textarea.' . $thiskey[1] . '][loadeditor]" class="inputbox" size="1" >';
                                $inserthtml .= '	<option value="1"' . ($array['loadeditor'] ? " selected=\"true\" " : "") . '>Yes</option>';
                                $inserthtml .= '	<option value="0"' . ($array['loadeditor'] ? "" : " selected=\"true\" ") . '>No</option>';
                                $inserthtml .= '</select></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "image":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Image</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Default directory</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][directory]" value="' . $array['directory'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "link":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Link</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "email":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Email</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "selectlist":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Select List</legend></i><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Options (one to a line)</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][options]" >' . $array['options'] . '</textarea></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "multiselect":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Multi Select</legend></i><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Name</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>Options (one to a line)</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][options]" >' . $array['options'] . '</textarea></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;

                        }
                        $customFieldId = $thiskey[1];
                    }
                    $inserthtml .= "<script>jQuery.noConflict();";
                    $inserthtml .= "jQuery('.deletefield').click(function(){";
                    $inserthtml .= "    if (confirm('Delete this field?')) {";
                    $inserthtml .= "       jQuery(this).tooltip('hide');";
                    $inserthtml .= "       jQuery(this).parent().remove();";
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
        <h4><?php echo JText::_('COM_FOCALPOINT_LOCATIONTYPE_CUSTOM_FIELDS_DESCRIPTION'); ?></h4>
        <dl class="adminformlist">
            <dd><a id="add-textbox" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Textbox</a>
            </dd>
            <dd><a id="add-textarea" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Textarea</a>
            </dd>
            <dd><a id="add-image" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Image</a></dd>
            <dd><a id="add-link" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Link</a></dd>
            <dd><a id="add-email" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Email</a></dd>
            <dd><a id="add-selectlist" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Select List</a></dd>
            <dd><a id="add-multiselect" class="btn btn-small element-add" href="#"><i class="icon-plus"></i> Multi Select</a></dd>
        </dl>
    </div>
    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>

</form>


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

        jQuery('#add-textbox').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Textbox</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field required" name="jform[custom][textbox.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-textarea').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Textarea</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][textarea.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Load Editor</label></div><div class="controls"><select name="jform[custom][textarea.' + id + '][loadeditor]" class="inputbox" size="1" ></div></div>';
            inserthtml = inserthtml + '	<option value="1" selected="selected">Yes</option>';
            inserthtml = inserthtml + '	<option value="0">No</option>';
            inserthtml = inserthtml + '</select></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-image').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Image</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][image.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Default directory</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][directory]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-link').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Link</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][link.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-email').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Email</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][email.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-selectlist').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Select List</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][selectlist.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Options (one to a line)</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][selectlist.' + id + '][options]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-multiselect').click(function () {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;Multi Select</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required">Name</label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][multiselect.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Tool tip</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Label</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label>Options (one to a line)</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][multiselect.' + id + '][options]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function () {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });
    })


</script>
