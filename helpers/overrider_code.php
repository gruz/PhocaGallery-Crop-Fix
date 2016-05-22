<?php
defined('_JEXEC') or die;
				/* ##mygruz20160521012924 { Added : */
//~ dump ($src,'$src');
//~ dump ($dst,'$dst');
//~ dump ($w,$h);
//~ dump ($fileIn);
				$path		= PhocaGalleryPath::getPath();
				$fileinDB = str_replace($path->image_abs,'',$fileIn);
				$db 		= JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select(array('params'));
				$query->from('#__phocagallery');
				$query->where($db->qn('filename') .'= '.$db->q($fileinDB));
				$db->setQuery($query);
				$item =  $db->loadResult();
				$crop_type = 'center';
				if (!empty($item)) {
					$params = new JRegistry;
					$params->loadString($item, 'JSON'); // Load my plugin params.
					$crop_type = $params->get('crop_type','center');
				}
//~ dump ($crop_type,'$crop_type');
				switch ($crop_type) {
					case 'top':
						$src[1] = 0;
						break;
					case 'bottom':
						$src[1] = $h-$src[3];
						break;
					case 'center':
					default :
						break;
				}
				if (empty($GLOBALS['../plugins/system/phocagalleryfix/images/phoca.gif'])) {
					echo '<img src=\'../plugins/system/phocagalleryfix/images/phoca.gif\' align=\'left\' style=\'margin:  0; padding: 0; \' /><br/>';
					$GLOBALS['../plugins/system/phocagalleryfix/images/phoca.gif'] = true;
				}
				/* ##mygruz20160521012924 } */
