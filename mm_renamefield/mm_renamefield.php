<?php
/**
 * mm_renameField
 * @version 1.2.2 (2016-10-31)
 * 
 * @desc A widget for ManagerManager plugin that allows one of the default document fields or template variables to be renamed within the manager.
 * 
 * @uses (MODX)EvolutionCMS >= 1.1
 * @uses (MODX)EvolutionCMS.plugins.ManagerManager plugin >= 0.7
 * 
 * @param $params {stdClass|arrayAssociative} — Parameters, the pass-by-name style is used. @required
 * @param $params->fields {stringCommaSeparated} — The name(s) of the document fields (or TVs) this should apply to. @required
 * @param $params->newLabel {string} — The new text for the label. @required
 * @param $params->roles {stringCommaSeparated} — The roles that the widget is applied to (when this parameter is empty then widget is applied to the all roles).
 * @param $params->templates {stringCommaSeparated} — Id of the templates to which this widget is applied (when this parameter is empty then widget is applied to the all templates).
 * @param $params->newHelp {string} — New text for the help icon with this field or for comment with TV. The same restriction apply as when using mm_changeFieldHelp directly.
 * 
 * @link https://code.divandesign.biz/modx/mm_renamefield
 * 
 * @copyright 2011–2016
 */

function mm_renameField($params){
	//For backward compatibility
	if (
		!is_array($params) &&
		!is_object($params)
	){
		//Convert ordered list of params to named
		$params = ddTools::orderedParamsToNamed([
			'paramsList' => func_get_args(),
			'compliance' => [
				'fields',
				'newLabel',
				'roles',
				'templates',
				'newHelp'
			]
		]);
	}
	
	$params = \DDTools\ObjectTools::extend([
		'objects' => [
			//Defaults
			(object) [
				'roles' => '',
				'templates' => '',
				'newHelp' => ''
			],
			$params
		]
	]);
	
	global $modx;
	$e = &$modx->Event;
	
	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if (
		$e->name == 'OnDocFormRender' &&
		useThisRule(
			$params->roles,
			$params->templates
		)
	){
		$params->fields = makeArray($params->fields);
		if (count($params->fields) == 0){
			return;
		}
		
		$output = '//---------- mm_renameField :: Begin -----' . PHP_EOL;
		
		foreach (
			$params->fields as
			$field
		){
			$element = '';
			
			switch ($field){
				// Exceptions
				case 'which_editor':
					$element = '$j("#which_editor").prev("span.warning")';
				break;
				
				case 'content':
					$element = '$j("#content_header")';
				break;
				
				// Ones that follow the regular pattern
				default:
					global $mm_fields;
					
					if (isset($mm_fields[$field])){
						$element = '$j.ddMM.fields.' . $field . '.$elem.parents("td:first").prev("td").children("span.warning")';
					}
				break;
			}
			
			if ($element != ''){
				$output .= $element.'.contents().filter(function(){return this.nodeType === 3;}).replaceWith("' . jsSafe($params->newLabel) . '");' . PHP_EOL;
			}
			
			// If new help has been supplied, do that too
			if ($params->newHelp != ''){
				mm_changeFieldHelp(
					$field,
					$params->newHelp,
					$params->roles,
					$params->templates
				);
			}
		}
		
		$output .= '//---------- mm_renameField :: End -----' . PHP_EOL;
		
		$e->output($output);
	}
}
?>