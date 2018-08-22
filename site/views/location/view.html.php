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


/**
 * View to edit
 */
class FocalpointViewLocation extends JViewLegacy {

    protected $state;
    protected $item;
    protected $form;
    protected $params;
	protected $outputfield;

    /**
     * Display the view
     */
   
    public function display($tpl = null)
	{
		$app                = JFactory::getApplication();
        $user               = JFactory::getUser();
        $this->state        = $this->get('State');
        $this->item         = $this->get('Data');
        $this->params       = $app->getParams('com_focalpoint');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        // Load FocalPoint Plugins. Trigger onBeforeMapPrepareRender
        JPluginHelper::importPlugin('focalpoint');
        JFactory::getApplication()->triggerEvent('onBeforeMapPrepareRender', array(&$this->item));
        
        if($this->_layout == 'edit') {
            $authorised = $user->authorise('core.create', 'com_focalpoint');
            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }
        }
        
        // Load the item metadata. Decode from JSON so the metadata can be accessed as an object
        $metadata = new JRegistry;
        $metadata->loadString($this->item->metadata, 'JSON');
        $this->item->metadata = $metadata;
        
        $this->_prepareDocument();
        
        // Load the item params. Decode from JSON so the parameters can be accessed as an object
        $params = new JRegistry;
        $params->loadString($this->item->params, 'JSON');
        $this->item->params = $params;

        // Merge global params with item params
        $params = clone $this->params;
        $params->merge($this->item->params);
        $this->item->params = $params;

        // Call system plugin onContentPrepare. This only works on fields called text.
        JPluginHelper::importPlugin('content');
        $this->item->text = $this->item->description;
        JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_focalpoint.location', &$this->item, &$this->params, $limitstart = 0));
        $this->item->description = $this->item->text;

        $this->item->text = $this->item->fulldescription;
        JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_focalpoint.location', &$this->item, &$this->params, $limitstart = 0));
        $this->item->fulldescription = $this->item->text;

        $this->item->description = $this->replace_custom_field_tags($this->item->description);
        $this->item->fulldescription = $this->replace_custom_field_tags($this->item->fulldescription);

        parent::display($tpl);
    }

    /**
     * Replaces all custom field tags in the text.
     *
     */
    public function replace_custom_field_tags($text){
        $regex		= '/{(.*?)}/i';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (!empty($matches)){

            // Cycle through each matching tag
            foreach ($matches as $match){

                // Output the relevant custom field if the tag matches the name.
                foreach($this->item->customfields as $name=>$customfield) {
                    if ($name == $match[1]) {

                        // Set up the custom field object for the sub template
                        $this->outputfield = new stdClass();
                        $this->outputfield->hidelabel = true;
                        $this->outputfield->data = $customfield->data;

                        // Buffer the output and load the default sub template.
                        ob_start();
                        echo $this->loadTemplate('customfield_'.$customfield->datatype);
                        $output = ob_get_contents();
                        ob_end_clean();

                        // Do the replace
                        $text = str_replace($match[0],$output, $text);
                    }
                }
            }

        }
        return $text;
    }

    /**
	 * Prepares the document by setting up page titles and metadata.
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		//Grab the active menu item. May return false.
		$menu = $menus->getActive();

		if ($menu) {
			// Use the Page Heading if defined in the menu
			if ($menu->params->get('show_page_heading') && $menu->params->get('page_heading')) {
				$this->item->page_title = $menu->params->get('page_heading');
			}

			// Set the page title if defined in the menu
			if ($menu->params->get('page_title')) {
				$title = $menu->params->get('page_title');
			} else {
				$title = $this->item->title;
			}
		} else {
			// No menu active menu item so set the page title as the item title
			$title = $this->item->title;
		}

		// Append or prepend the sitename to the browser title as defined in Global Configuratio
		if (empty($title)) {
			$title = $app->get('sitename');
		} elseif ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		// Set the page title
		$this->document->setTitle($title);

		// Set the page meta description. Article Meta over rides menu meta.
		$articlemeta = ($this->item->metadata->get('metadesc'));
		if($articlemeta){
			$this->document->setDescription($this->item->metadata->get('metadesc'));
		} elseif ($menu) {
			if ($menu->params->get('menu-meta_description')) {
				$this->document->setDescription($this->params->get('menu-meta_description'));
			}
		}

		// Set the page keywords
		$articlekeywords = ($this->item->metadata->get('metakey'));
		if($articlekeywords){
			$this->document->setMetadata('keywords', $this->item->metadata->get('metakey'));
		} elseif ($menu) {
			if ($menu->params->get('menu-meta_keywords')) {
				$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}
		}

		// Set the robots declaration
		$articlerobots = ($this->item->metadata->get('robots'));
		if($articlerobots){
			$this->document->setMetadata('robots', $this->item->metadata->get('robots'));
		} elseif ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Set the robots declaration
		$articlerights = ($this->item->metadata->get('rights'));
		if($articlerights){
			$this->document->setMetadata('rights', $this->item->metadata->get('rights'));
		}

		// Set the author declaration
		$articleauthor = ($this->item->metadata->get('author'));
		if($articleauthor){
			$this->document->setMetadata('author', $this->item->metadata->get('author'));
		}
	}

	/**
	 * Renders a custom field using the relevant template.
	 *
	 * $field  single customfield object.
	 *
	 */
	public function renderField($field, $hidelabel = false) {
        $datatype   = $field->datatype;
        
        if ($field->data) {
            // We need to assign $field to a property of the view class for the data to be available in
            // the relevant subtemplate.
            $this->outputfield = $field;
			$this->outputfield->hidelabel = $hidelabel;

            switch ($datatype){
                case "textbox":
                    echo $this->loadTemplate('customfield_textbox');
                    break;
                case "link":
                    echo $this->loadTemplate('customfield_link');
                    break;
                case "email":
                    echo $this->loadTemplate('customfield_email');
                    break;
                case "textarea":
                    echo $this->loadTemplate('customfield_textarea');
                    break;
                case "image":
                    echo $this->loadTemplate('customfield_image');
                    break;
                case "selectlist":
                    echo $this->loadTemplate('customfield_selectlist');
                    break;
                case "multiselect":
                    echo $this->loadTemplate('customfield_multiselect');
                    break;
            }
            unset($this->outputfield);
			return;
        }
    }

	/**
	 * Renders a single field using the relevant template.
	 * $my_field is the name of the custom field.
	 */
	public function renderCustomField($my_field, $hidelabel = false)
	{
		if (isset($this->item->customfields->{$my_field})){
			return $this->renderField($this->item->customfields->{$my_field}, $hidelabel);
		} else {
			return false;
		}
	}
}
