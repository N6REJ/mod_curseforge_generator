<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

// 1. Handle AJAX requests early
$input = Factory::getApplication()->input;
if ($input->get('format') === 'json' && $input->getBool('xajax', false))
{
    require_once __DIR__ . '/helper.php';
    ModcurseforgeGeneratorHelper::ajaxGenerate();
    return;
}

// 2. Fetch module configuration fallback variables
$defaultOwner  = $params->get('default_owner', 'N6REJ');
$defaultRepo   = $params->get('default_repo', 'curseforge-downloads-action');
$defaultBranch = $params->get('default_branch', 'stats-data');
$defaultStyle  = $params->get('default_style', 'flat-square');

// 3. Directly load files (Bypasses all JSON asset files and registry lookups completely)
$doc = Factory::getApplication()->getDocument();
$mediaUrl = Joomla\CMS\URI\URI::root() . 'media/mod_curseforge_generator/';

// Inject asset files directly into the template document header structure
$doc->addStyleSheet($mediaUrl . 'css/curseforge-generate.css');
$doc->addScript($mediaUrl . 'js/curseforge-generator.js', [], ['defer' => true]);

// 4. Generate URL endpoints and capture URL state params
$ajaxUrl   = Route::_('index.php?format=json&xajax=1', false);
$urlMetric = $input->get('badge-metric', '', 'STRING');
$urlColor  = $input->get('badge-color', '', 'STRING');
$urlLabel  = $input->get('badge-label', '', 'STRING');
$urlTab    = $input->get('tab', 'badge', 'STRING');

// 5. Pass parameters to JavaScript container safely
$doc->addScriptOptions('mod_curseforge_generator', [
    'defaultOwner'  => $defaultOwner,
    'defaultRepo'   => $defaultRepo,
    'defaultBranch' => $defaultBranch,
    'defaultStyle'  => $defaultStyle,
    'ajaxUrl'       => $ajaxUrl,
    'urlMetric'     => $urlMetric,
    'urlColor'      => $urlColor,
    'urlLabel'      => $urlLabel,
    'urlTab'        => $urlTab,
]);

// 6. Load layout file
require ModuleHelper::getLayoutPath('mod_curseforge_generator', $params->get('layout', 'default'));
