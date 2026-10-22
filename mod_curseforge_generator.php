<?php
/**
 * @package     mod_curseforge_generator
 * @subpackage  mod_curseforge_generator
 * @copyright   (C) 2026 N6REJ
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 only.
 * See LICENSE for the complete license text.
 * @link        https://github.com/N6REJ/mod_curseforge_generator
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

// Module helper (metric map, colour defaults, badge/chart builders)
require_once __DIR__ . '/helper.php';

$input = Factory::getApplication()->input;

// 2. Fetch module configuration fallback variables
$defaultOwner    = $params->get('default_owner', 'N6REJ');
$defaultRepo     = $params->get('default_repo', 'Bears-boon-viewer');
$defaultBranch   = $params->get('default_branch', 'main');
$defaultJsonPath = $params->get('default_jsonpath', 'stats/downloads.json');
$defaultStyle    = $params->get('default_style', 'flat-square');

// 3. Load Bootstrap 5 only if the site template has not already loaded it
$doc = Factory::getApplication()->getDocument();
$wa  = $doc->getWebAssetManager();

if (!$wa->isAssetActive('style', 'bootstrap.css') && $wa->assetExists('preset', 'bootstrap'))
{
    $wa->usePreset('bootstrap');
}

// Load all module assets directly from this file
$mediaUrl = Joomla\CMS\URI\URI::root() . 'media/mod_curseforge_generator/';

$doc->addScript($mediaUrl . 'js/curseforge-generator.js', [], ['defer' => true]);

// Module stylesheet (colour well alignment, output blocks)
$wa->registerAndUseStyle('mod_curseforge_generator', 'mod_curseforge_generator/curseforge-generator.css');

// 4. Generate URL endpoints and capture URL state params
$ajaxUrl   = Route::_('index.php?option=com_ajax&module=curseforge_generator&format=raw', false);
$urlMetric = $input->get('badge-metric', '', 'STRING');
$urlColor  = $input->get('badge-color', '', 'STRING');
$urlLabel  = $input->get('badge-label', '', 'STRING');
$urlTab    = $input->get('tab', 'badge', 'STRING');

// 5. Pass parameters to JavaScript container safely
$doc->addScriptOptions('mod_curseforge_generator', [
    'defaultOwner'  => $defaultOwner,
    'defaultRepo'   => $defaultRepo,
    'defaultBranch' => $defaultBranch,
    'defaultJsonPath' => $defaultJsonPath,
    'defaultStyle'  => $defaultStyle,
    'ajaxUrl'       => $ajaxUrl,
    'urlMetric'     => $urlMetric,
    'urlColor'      => $urlColor,
    'urlLabel'      => $urlLabel,
    'urlTab'        => $urlTab,
]);

// 6. Load layout file
require ModuleHelper::getLayoutPath('mod_curseforge_generator', $params->get('layout', 'default'));
