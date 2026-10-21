<?php

/**
 * @package     N6REJ.Module.CFDGGenerator
 * @subpackage  mod_cfdg_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$defaultOwner = $params->get('default_owner', 'N6REJ');
$defaultRepo  = $params->get('default_repo', 'curseforge-downloads-action');
$defaultBranch = $params->get('default_branch', 'stats-data');

require ModuleHelper::getLayoutPath('mod_cfdg_generator', $params->get('layout'));
