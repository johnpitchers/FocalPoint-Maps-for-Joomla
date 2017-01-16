<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class FocalpointViewLocation extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $form;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        //Get custom form fields
        $this->item->customformfieldshtml = self::getCustomFieldsHTML($this->item->type);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
        $canDo = FocalpointHelper::getActions();

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATION'), 'location.png');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {

            JToolBarHelper::apply('location.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('location.save', 'JTOOLBAR_SAVE');
        }
        if (!$checkedOut && ($canDo->get('core.create'))) {
            JToolBarHelper::custom('location.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::custom('location.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
        }
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
        }

    }

    /**
     * Function to retrieve the form elements defined in locationtypes and populate with saved values
     */
    public function getCustomFieldsHTML($type)
    {
        $model = $this->getModel();
        $result = $model->getCustomFieldsHTML($type);

        // First check the session for data. If the previous save failed with an error the data will be in the session so we can
        // repopulate the custom fields. Otherwise we lose unsaved info.
        $data = JFactory::getApplication()->getUserState('com_focalpoint.edit.location.data', array());
        if (isset($data['customfieldsdata'])) $this->item->custom = json_decode($data['customfieldsdata'], true);

        $inserthtml = "";
        if ($result) {
            foreach ($result as $key1 => $array) {
                $thiskey = explode(".", $key1);
                $value = isset($this->item->custom[$key1 . '.' . $array['name']]) ? $this->item->custom[$key1 . '.' . $array['name']] : "";
                switch ($thiskey[0]) {
                    case "textbox":
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . ']" value="' . $value . '" /></div></div>';
                        break;
                    case "textarea":
                        $inserthtml .= '<div><label class="hasTip" title="' . $array['description'] . '">' . $array['label'] . '</div>';
                        if ($array['loadeditor']) {
                            $conf = JFactory::getConfig();
                            $editorconf = $conf->get('editor');
                            $editor = JEditor::getInstance($editorconf);
                            $inserthtml .= '<div class="clrlft customeditor">' . $editor->display('jform[custom][' . $key1 . '.' . $array['name'] . ']', $value, '100%', '300px', NULL, NULL, true, str_replace(array("[", "]"), "", 'jform[custom][' . $key1 . '.' . $array['name'] . ']')) . '</div>';
                        } else {
                            $inserthtml .= '<textarea class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . ']" value="" >' . $value . '</textarea></li>';
                        }
                        break;
                    case "image":
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><div class="input-append"><input id="custimg-' . $thiskey[1] . '" name="jform[custom][' . $key1 . '.' . $array['name'] . ']" type="text" value="' . $value . '" /><a class="modal btn" href="index.php?option=com_media&view=images&tmpl=component&fieldid=custimg-' . $thiskey[1] . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}" >Select</a></div></div></div>';
                        break;
                    case "link":
                        if (!is_array($value)) {
                            //Define blank values so PHP doesn't generate notices
                            $value['url'] = "";
                            $value['linktext'] = "";
                            $value['target'] = "";
                        }
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . '][url]" value="' . $value['url'] . '" /><br><span class="hasTooltip small" title="The URL to link to. Include http:// at the start for external links.">URL</span></div></div>';
                        $inserthtml .= '<div class="control-group"><div class="control-label">&nbsp;</div><div class="controls"><input type="text" class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . '][linktext]" value="' . $value['linktext'] . '" /><br><span class="hasTooltip small" title="Optional link text. If left blank the URL will be used as link text.">Link text</span></div></div>';
                        $inserthtml .= '<div class="control-group"><div class="control-label">&nbsp;</div><div class="controls"><select name="jform[custom][' . $key1 . '.' . $array['name'] . '][target]" class="inputbox">';
                        $inserthtml .= '<option value="0" ' . ($value['target'] ? '' : 'selected="selected"') . '>No</option>';
                        $inserthtml .= '<option value="1" ' . ($value['target'] ? 'selected="selected"' : '') . '>Yes</option></select><br><span class="hasTooltip small" title="Open in new window?">New Window?</span></div></div>';
                        break;
                    case "email":
                        if (!is_array($value)) {
                            //Define blank values so PHP doesn't generate notices
                            $value['email'] = "";
                            $value['linktext'] = "";
                        }
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . '][email]" value="' . $value['email'] . '" /><br><span class="small hasTooltip" title="The actual email address. Do not include mailto:. This will be added automatically">email address</span></div></div>';
                        $inserthtml .= '<div class="control-group"><div class="control-label">&nbsp;</div><div class="controls"><input type="text" class="field" name="jform[custom][' . $key1 . '.' . $array['name'] . '][linktext]" value="' . $value['linktext'] . '" /><br><span class="small hasTooltip" title="Optional link text. If left blank the URL will be used as link text.">Link text</span></div></div>';
                        break;
                    case "selectlist":
                        $array['optionlist'] = explode("\n", $array['options']);
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><select name="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="inputbox">';
                        foreach ($array['optionlist'] as $opt){
                            $opt = str_replace(array("\r","\n"),"",$opt);
                            $inserthtml .= '<option value="'.$opt.'" ' . ($value==$opt?'selected="selected"':'') . '>'.$opt.'</option>';

                        }
                        $inserthtml .="</select></div></div>";
                        break;
                    case "multiselect":
                        $array['optionlist'] = explode("\n", $array['options']);
                        $inserthtml .= '<div class="control-group"><div class="control-label"><label id="jform[custom][' . $key1 . '.' . $array['name'] . ']-lbl" for="jform[custom][' . $key1 . '.' . $array['name'] . ']" class="hasTooltip" title="' . $array['description'] . '">' . $array['label'] . '</label></div><div class="controls"><select multiple name="jform[custom][' . $key1 . '.' . $array['name'] . '][]" class="inputbox">';
                        foreach ($array['optionlist'] as $opt){
                            $opt = str_replace(array("\r","\n"),"",$opt);
                            //Check if each option exists in the saved record for this location and mark as selected if so.
                            $selected = false;
                            if (is_array($value)) {
                                $in_array_at_pos = array_search($opt, $value);
                                if ($in_array_at_pos !== false){
                                    $selected = true;
                                    unset($value[$in_array_at_pos]);
                                }
                            }
                            $inserthtml .= '<option value="'.$opt.'" ' . ($selected?'selected="selected"':'') . '>'.$opt.'</option>';

                        }
                        $inserthtml .="</select></div></div>";
                        break;
                }
                $inserthtml .= "<hr />";
            }

        } else {
            if ($this->item->type == "") {
                $inserthtml = JText::_('COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_SAVE_FIRST');
            } else {
                $inserthtml = JText::_('COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_NONE_DEFINED');
            }

        }
        return $inserthtml;
    }
}