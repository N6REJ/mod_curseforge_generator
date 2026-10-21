<?php

/**
 * @package     mod_curseforge_generator
 * @subpackage  mod_curseforge_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Handle AJAX requests
$input = Factory::getApplication()->input;

if ($input->get('format') === 'json' && $input->getBool('xajax', false))
{
    require __DIR__ . '/helper.php';
    ModcurseforgeGeneratorHelper::ajaxGenerate();
    return;
}

$defaultOwner  = $params->get('default_owner', 'N6REJ');
$defaultRepo   = $params->get('default_repo', 'curseforge-downloads-action');
$defaultBranch = $params->get('default_branch', 'stats-data');

require ModuleHelper::getLayoutPath('mod_curseforge_generator', $params->get('layout'));
