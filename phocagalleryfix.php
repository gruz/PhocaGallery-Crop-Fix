<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagecode
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Language Code plugin class.
 *
 * @since  2.5
 */
class PlgSystemPhocagalleryfix extends JPlugin
{

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$jinput = JFactory::getApplication()->input; if ($jinput->get('option',null) == 'com_dump') { return; }


		// Load languge for frontend
		$this->plg_name = $config['name'];
		$this->plg_type = $config['type'];
		$this->plg_full_name = 'plg_'.$config['type'].'_'.$config['name'];
		$this->langShortCode = null;//is used for building joomfish links
		$this->default_lang = JComponentHelper::getParams('com_languages')->get('site');
		$language = JFactory::getLanguage();
		$this->plg_path = JPATH_PLUGINS.'/'.$this->plg_type.'/'.$this->plg_name.'/';
		$user = JFactory::getUser();
		$user_language = $user->getParam('admin_language');
		$language->load($this->plg_full_name, $this->plg_path, 'en-GB', true);
		if ($user_language != 'en-GB') {
			$language->load($this->plg_full_name, $this->plg_path, $this->default_lang, true);
			$language->load($this->plg_full_name, $this->plg_path, $user_language, true);
		}


		$session = JFactory::getSession();
		$option = $jinput->get('option',null);
		if ($option != 'com_phocagallery') { return;}
		$task = $jinput->get('task',null);
		$view = $jinput->get('view',null);
//~ dumpMessage('$option = '.$option. ' | $task = '.$task.' | $view ='.$view);
//dump ($jinput->post->getArray(),'post');
//  [string] $task = "phocagalleryimg.edit" Tooltip

//[string] $task = "phocagalleryimg.apply" Tooltip
//[string] $task = "phocagalleryimg.save" Tooltip
//[string] $task = "phocagalleryimg.save2new" Tooltip

		if (in_array ($task,array('phocagalleryimg.apply','phocagalleryimg.save','phocagalleryimg.save2new'))) {
			// If the plugin which is saved is our current image and it's enabled
			$session = JFactory::getSession();
			$jform = $jinput->post->get('jform',null,'array');
			$imageID = $jinput->get('id',null,'int');
//~ dump ($imageID,'$imageID');
//~ dump ($jform,'$jform');
			$session = JFactory::getSession();
			$crop_types = $session->get('crop_types',array(),$this->plg_name);
//~ dump ($crop_types,'$crop_types');
			if (isset($crop_types[$imageID]) && $crop_types[$imageID] == $jform['params']['crop_type'] ) {
//~ dump ($crop_types[$imageID],'$crop_types['.$imageID.']');
				unset($crop_types[$imageID]);
				$session->set('crop_types',$crop_types,$this->plg_name);
			}
			else if (isset($crop_types[$imageID]) && $crop_types[$imageID] != $jform['params']['crop_type'] ) {
//~ dumpMessage ('RECREATE IS NEEDED');
				unset($crop_types[$imageID]);

				$uri = JFactory::getURI();
				switch ($task) {
					case 'phocagalleryimg.apply':
						$return = '&return='.base64_encode('index.php?option=com_phocagallery&task=phocagalleryimg.edit&id='.$imageID);
						break;
					case 'phocagalleryimg.save2new':
						$return = '&return='.base64_encode('index.php?option=com_phocagallery&task=phocagalleryimg.edit');
						break;
					case 'phocagalleryimg.save':
					default :
						$return = '';
						break;
				}

				//$session->set('crop_types',$crop_types,$this->plg_name);


				$session->set('crop_types',$crop_types,$this->plg_name);
				$link = 'index.php?option=com_phocagallery&task=phocagalleryimg.recreate&cid[]='.$imageID.$return;
				$session->set('recreate_link',$link,$this->plg_name);
				//$app	= JFactory::getApplication();
				//$app->redirect($link);
			}

			//$session->set('crop_types',$crop_types,$this->plg_name);


		}
		else if (in_array ($task,array('phocagalleryimg.recreate'))) {
			$imageID = $jinput->get('id',null,'int');
			$return = $jinput->get('return',null,'raw');
			$crop_type = $jinput->get('crop_type',null,'raw');
//~ dump ($crop_type,'$crop_type');
			if (!empty($crop_type)) {
				$id = $jinput->get('cid',null,'array');
				$id = $id[0];
				if (!class_exists('TablePhocaGallery')) { include JPATH_ROOT.'/administrator/components/com_phocagallery/tables/phocagallery.php';	}
				$imageTable = JTable::getInstance('PhocaGallery','Table');
				$imageTable->load($id);
				$params = json_decode($imageTable->params);
				if (empty($params)) {
					$params = new stdClass;
				}
				$params->crop_type = $crop_type;
				$imageTable->params = json_encode($params);
				$imageTable->store();
				return;
			}

//~ dump ($return,'recreate');
//~ dump (base64_decode($return),'recreate');
			if (!empty($return)) {
				$link = base64_decode($return);
				$session = JFactory::getSession();
				$session->set('return_link',$link,$this->plg_name);
			}
		}
		else if (empty($task)) {
//~ $recreate_link = $session->get('recreate_link',null,$this->plg_name);
//~ $return_link = $session->get('return_link',null,$this->plg_name);
//~ dump ($recreate_link,'$recreate_link');
//~ dump ($return_link,'$return_link');
			$session = JFactory::getSession();
			$app	= JFactory::getApplication();
			$link = $session->get('recreate_link',null,$this->plg_name);
			$session->set('recreate_link',null,$this->plg_name);
			if (!empty($link)) {
//~ dumpMessage('Redirect to recreate!');
				$app->redirect($link);
			}
			if ($view == 'phocagalleryimgs') {
				$link = $session->get('return_link',null,$this->plg_name);
				$session->set('return_link',null,$this->plg_name);
				if (!empty($link) ) {
					$session->set('return_link2',$link,$this->plg_name);
//~ dumpMessage('first time - not yet ');
					return;
					//$app->redirect($link);
				}
				$link = $session->get('return_link2',null,$this->plg_name);
				$session->set('return_link2',null,$this->plg_name);
				if (!empty($link) ) {
					$session->set('return_link2',null,$this->plg_name);
//~ dumpMessage('GO HOME ');
					$app->redirect($link);
					return;
				}
			}
		}
	}

	/**
	 * Plugin that changes the language code used in the <html /> tag.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterRender()
	{
		$jinput = JFactory::getApplication()->input; if ($jinput->get('option',null) == 'com_dump') { return; }
		$app = JFactory::getApplication();
		if ($app->isSite()) { return; }

		$option = $jinput->get('option',null);
		$view = $jinput->get('view',null);
		//~ $task = $jinput->get('task',null);
//~ dump ($view,$task);
		if ($option != 'com_phocagallery' || $view != 'phocagalleryimgs') { return;}

//~ dumpMessage('$option = '.$option. ' | $task = '.$task.' | $view ='.$view);
//dump ($jinput->post->getArray(),'post');
//  [string] $task = "phocagalleryimg.edit" Tooltip

		$body = $app->getBody();
		preg_match_all("/<a[^>]*href=\"\/administrator\/index\.php\?option=com_phocagallery&amp;task=phocagalleryimg\.recreate&amp;cid\[\]=([0-9]+)[^0-9>]*>.*<\/a>[^<]*<a[^>]*onclick=\"window\.location\.reload\(true\);\".*>.*<\/a>/Ui", $body, $matches);
		$crop_types = array('top','center','bottom');
		if (empty($matches) || empty($matches[0])) { return;}

		$style = '
				border-color: silver;
				border-style: solid;
				margin:0 1px 0 0;
				float: left;
				height: 13px;
				width: 12px;
			';
		$css = '
			.crop_top {
				'.$style.'
				border-width: 1px 1px 8px 1px;
			}
			.crop_center {
				'.$style.'
				border-width: 5px 1px 5px 1px;
				height:12px
			}
			.crop_bottom {
				'.$style.'
				border-width: 8px 1px 1px 1px;
			}
			.crop_top:hover, .crop_center:hover, .crop_bottom:hover {
				border-color:black;
			}
		';
		//$document = JFactory::getDocument();
		//$document->addStyleDeclaration($css);
		$body = str_replace('</head>','<style>'.$css.'</style></head>',$body);

		foreach ($matches[1] as $k=>$v) {
			$link = 'index.php?option=com_phocagallery&amp;task=phocagalleryimg.recreate&amp;cid[]='.$v.'&amp;crop_type=';
			$icons = array();
			$icons[] = '<a href="'.$link.'top" class="crop_top hasTooltip" title="'.JText::_('PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_TOP').'"></a>';
			$icons[] = '<a href="'.$link.'center" class="crop_center hasTooltip" title="'.JText::_('PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_CENTER').'"></a>';
			$icons[] = '<a href="'.$link.'bottom" class="crop_bottom hasTooltip" title="'.JText::_('PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_BOTTOM').'"></a>';
			$body = str_replace($matches[0][$k],$matches[0][$k].implode(' ',$icons),$body);
		}
		$app->setBody($body);

	}


	/**
	 * Prepare form.
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since	2.5
	 */
	public function onContentPrepareForm($form, $data) {
		// Check we have a form.
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}
		// Check we are manipulating the languagecode plugin.
		if ($form->getName() != 'com_phocagallery.phocagalleryimg' )
		{
			return true;
		}

		if (!empty($data->params) && !empty(!empty($data->params['crop_type']))) {
			$session = JFactory::getSession();
			$crop_types = $session->get('crop_types',array(),$this->plg_name);
			$crop_types[$data->id] = $data->params['crop_type'];
			$session->set('crop_types',$crop_types,$this->plg_name);
		}

		$form->load('
			<form>
				<fields name="params">
					<fieldset
						name="publish"
					>
						<field
							name="crop_type"
							default="center"
							type="list"
							description="' . JText::_('PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_DESC') . '"
							translate_description="false"
							label="' . JText::_('PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP') . '"
						>
							<option value="top">PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_TOP</option>
							<option value="center">PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_CENTER</option>
							<option value="bottom">PLG_SYSTEM_PHOCAGALLERYFIX_HOW_TO_CROP_BOTTOM</option>
						</field>
					</fieldset>
				</fields>
			</form>
		');
		return true;
	}


		public function onAfterInitialise() {

			$jinput = JFactory::getApplication()->input; if ($jinput->get('option',null) == 'com_dump') { return; }

			$app = JFactory::getApplication();
			if ($app->isSite()) { return; }

			$option = $this->getOption();
			if ($option !== 'com_phocagallery') { return; }

			jimport('joomla.filesystem.file');

			if (!class_exists('PhocaGalleryLoader')) { include JPATH_ROOT.'/administrator/components/com_phocagallery/libraries/loader.php';	}
			$buffer = JFile::read (JPATH_ROOT.'/administrator/components/com_phocagallery/libraries/phocagallery/image/imagemagic.php');
			$overrider_code = '?>'.file_get_contents(dirname(__FILE__).'/helpers/overrider_code.php');
			$buffer = str_replace('ImageCopyResampled(',$overrider_code.PHP_EOL.'ImageCopyResampled(',$buffer);
			eval('?>'.$buffer.PHP_EOL);


		}

		/**
		 * Get's current $option as it's not defined at onAfterInitialize
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return	string			Option, i.e. com_content
		 */
		function getOption() {
			$jinput = JFactory::getApplication()->input;
			$option = $jinput->get('option',null);
			if(empty($option) && JFactory::getApplication()->isSite() ) {
				$app = JFactory::getApplication();
				$router = $app->getRouter();
				$uri     = JUri::getInstance();
				JURI::current();// It's very strange, but without this line at least Joomla 3 fails to fulfill the parse below task
				$parsed = $router->parse($uri);
				$option = $parsed['option'];
				/*
				$menuDefault = JFactory::getApplication()->getMenu()->getDefault();
				if (is_int($menuDefault) && $menuDefault == 0) return;
				$componentID = $menuDefault->component_id;
				$db = JFactory::getDBO();
				$db->setQuery('SELECT * FROM #__extensions WHERE extension_id ='.$db->quote($componentID));
				$component = $db->loadObject();
				$option = $component->element;
				*/
			}
			return $option;
		}

}
