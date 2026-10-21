<?php

/**
 * @package     N6REJ.Module.curseforgeGenerator
 * @subpackage  mod_curseforge_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load Web Assets
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_curseforge_generator.style', 'mod_curseforge_generator/style.css');
$wa->registerAndUseScript('mod_curseforge_generator.script', 'mod_curseforge_generator/curseforge-generator.js', [], ['defer' => true], ['core']);

// AJAX endpoint URL
$ajaxUrl = Route::_('index.php?format=json&xajax=1', false);

// Pass default values to JS
$doc = Factory::getDocument();
$doc->addScriptOptions('mod_curseforge_generator', [
    'defaultOwner'  => $defaultOwner,
    'defaultRepo'   => $defaultRepo,
    'defaultBranch' => $defaultBranch,
    'ajaxUrl'       => $ajaxUrl,
]);
?>

<div class="mod-curseforge-generator <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')); ?>">
    <div class="curseforge" id="curseforge-root">
        <p class="curseforge-intro">
            <?php echo Text::_('MOD_curseforge_GENERATOR_INTRO'); ?>
            <a href="https://github.com/N6REJ/curseforge-downloads-action" target="_blank" rel="noopener">curseforge-downloads-action</a>.
        </p>

        <!-- Generator Lab Header -->
        <div class="curseforge-lab-header">
            <h2 class="curseforge-lab-title"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_TITLE'); ?></h2>
            <p class="curseforge-lab-desc"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_DESC'); ?></p>
            <p class="curseforge-lab-tabs-hint"><?php echo Text::_('MOD_curseforge_GENERATOR_TABS_HINT'); ?></p>
        </div>

        <!-- Tabs: Basic / Advanced -->
        <div class="curseforge-tabs" role="tablist">
            <button class="curseforge-tab curseforge-tab-active" role="tab" data-tab="basic" aria-selected="true">
                <?php echo Text::_('MOD_curseforge_GENERATOR_TAB_BASIC'); ?>
            </button>
            <button class="curseforge-tab" role="tab" data-tab="advanced" aria-selected="false">
                <?php echo Text::_('MOD_curseforge_GENERATOR_TAB_ADVANCED'); ?>
            </button>
        </div>

        <form class="curseforge-form" id="curseforge-form" autocomplete="off">

            <!-- Basic Tab Panel -->
            <div class="curseforge-panel curseforge-panel-active" id="curseforge-panel-basic" role="tabpanel">
                <div class="curseforge-field-row">
                    <div class="curseforge-field">
                        <label for="curseforge-owner" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_OWNER'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-owner" value="<?php echo htmlspecialchars($defaultOwner); ?>" spellcheck="false" />
                    </div>
                    <div class="curseforge-field">
                        <label for="curseforge-repo" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_REPO'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-repo" value="<?php echo htmlspecialchars($defaultRepo); ?>" spellcheck="false" />
                    </div>
                </div>
            </div>

            <!-- Advanced Tab Panel -->
            <div class="curseforge-panel" id="curseforge-panel-advanced" role="tabpanel">
                <div class="curseforge-field-row">
                    <div class="curseforge-field">
                        <label for="curseforge-branch" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_BRANCH'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-branch" value="<?php echo htmlspecialchars($defaultBranch); ?>" spellcheck="false" />
                    </div>
                    <div class="curseforge-field">
                        <label for="curseforge-jsonpath" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_JSONPATH'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-jsonpath" value="curseforge/downloads.json" spellcheck="false" />
                    </div>
                </div>
                <div class="curseforge-field-row">
                    <div class="curseforge-field">
                        <label for="curseforge-chartpath" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTPATH'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-chartpath" value="curseforge/downloads-trend.svg" spellcheck="false" />
                    </div>
                    <div class="curseforge-field">
                        <label for="curseforge-chartdir" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTDIR'); ?></label>
                        <input type="text" class="curseforge-input" id="curseforge-chartdir" value="curseforge/charts" spellcheck="false" />
                    </div>
                </div>
            </div>
        </form>

        <!-- Generate Badge Section -->
        <div class="curseforge-section">
            <h3 class="curseforge-section-title"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_BADGE'); ?></h3>
            <p class="curseforge-section-desc"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_BADGE_DESC'); ?></p>

            <div class="curseforge-field-row">
                <div class="curseforge-field">
                    <label for="curseforge-metric" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_METRIC'); ?></label>
                    <select class="curseforge-select" id="curseforge-metric">
                        <option value="total|$.stats.total|0A7EA4"><?php echo Text::_('MOD_curseforge_GENERATOR_TOTAL'); ?></option>
                        <option value="day|$.stats.day|1E8E3E"><?php echo Text::_('MOD_curseforge_GENERATOR_DAY'); ?></option>
                        <option value="week|$.stats.week|0069C2"><?php echo Text::_('MOD_curseforge_GENERATOR_WEEK'); ?></option>
                        <option value="month|$.stats.month|B26A00"><?php echo Text::_('MOD_curseforge_GENERATOR_MONTH'); ?></option>
                        <option value="year|$.stats.year|6A1B9A"><?php echo Text::_('MOD_curseforge_GENERATOR_YEAR'); ?></option>
                        <option value="profile|$.profile.defaultMode|5E35B1"><?php echo Text::_('MOD_curseforge_GENERATOR_PROFILE'); ?></option>
                    </select>
                </div>
                <div class="curseforge-field">
                    <label for="curseforge-label" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_LABEL'); ?></label>
                    <input type="text" class="curseforge-input" id="curseforge-label" value="downloads/total" spellcheck="false" />
                </div>
                <div class="curseforge-field">
                    <label for="curseforge-color" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_COLOR'); ?></label>
                    <div class="curseforge-color-wrapper">
                        <input type="color" class="curseforge-color-picker" id="curseforge-color-picker" value="#0A7EA4" />
                        <input type="text" class="curseforge-input curseforge-color-text" id="curseforge-color" value="0A7EA4" spellcheck="false" maxlength="6" />
                    </div>
                </div>
            </div>

            <!-- Badge Preview -->
            <div class="curseforge-preview-area" id="curseforge-badge-preview-area">
                <div class="curseforge-preview-label"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW_LABEL'); ?></div>
                <div class="curseforge-preview-placeholder" id="curseforge-badge-placeholder">
                    <svg class="curseforge-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h12"/></svg>
                    <span><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></span>
                </div>
                <div class="curseforge-preview-content" id="curseforge-badge-content" style="display:none;">
                    <img class="curseforge-preview-img" id="curseforge-badge-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW'); ?>" />
                </div>
            </div>

            <!-- Badge Output -->
            <div class="curseforge-output-group">
                <div class="curseforge-output-label"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_URL_LABEL'); ?></div>
                <div class="curseforge-code-block">
                    <code id="curseforge-badge-url"></code>
                </div>
                <div class="curseforge-output-label"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></div>
                <div class="curseforge-code-block">
                    <code id="curseforge-badge-md"></code>
                </div>
                <div class="curseforge-actions">
                    <button type="button" class="curseforge-btn curseforge-btn-primary" data-copy="curseforge-badge-md">
                        <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_MD'); ?>
                    </button>
                    <button type="button" class="curseforge-btn curseforge-btn-outline" data-copy="curseforge-badge-url">
                        <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_URL'); ?>
                    </button>
                </div>
            </div>
        </div>

        <hr class="curseforge-divider" />

        <!-- Generate Chart Section -->
        <div class="curseforge-section">
            <h3 class="curseforge-section-title"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_CHART'); ?></h3>
            <p class="curseforge-section-desc"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_CHART_DESC'); ?></p>

            <div class="curseforge-field-row">
                <div class="curseforge-field">
                    <label for="curseforge-charttype" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTTYPE'); ?></label>
                    <select class="curseforge-select" id="curseforge-charttype">
                        <option value="total-trend"><?php echo Text::_('MOD_curseforge_GENERATOR_TOTAL_TREND'); ?></option>
                        <option value="daily"><?php echo Text::_('MOD_curseforge_GENERATOR_DAILY'); ?></option>
                        <option value="weekly"><?php echo Text::_('MOD_curseforge_GENERATOR_WEEKLY'); ?></option>
                        <option value="monthly"><?php echo Text::_('MOD_curseforge_GENERATOR_MONTHLY'); ?></option>
                        <option value="yearly"><?php echo Text::_('MOD_curseforge_GENERATOR_YEARLY'); ?></option>
                    </select>
                </div>
                <div class="curseforge-field">
                    <label for="curseforge-theme" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME'); ?></label>
                    <select class="curseforge-select" id="curseforge-theme">
                        <option value="black"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_BLACK'); ?></option>
                        <option value="slate" selected><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_SLATE'); ?></option>
                        <option value="orange"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_ORANGE'); ?></option>
                    </select>
                </div>
            </div>

            <div class="curseforge-field-row">
                <div class="curseforge-field">
                    <label for="curseforge-chart-color" class="curseforge-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_COLOR'); ?></label>
                    <div class="curseforge-color-wrapper">
                        <input type="color" class="curseforge-color-picker" id="curseforge-chart-color-picker" value="#0A7EA4" />
                        <input type="text" class="curseforge-input curseforge-color-text" id="curseforge-chart-color" value="0A7EA4" spellcheck="false" maxlength="6" />
                    </div>
                </div>
            </div>

            <div class="curseforge-toggle-group">
                <div class="curseforge-toggle">
                    <input class="curseforge-toggle-input" type="checkbox" id="curseforge-primary" />
                    <label class="curseforge-toggle-label" for="curseforge-primary">
                        <span class="curseforge-toggle-slider"></span>
                        <span class="curseforge-toggle-text"><?php echo Text::_('MOD_curseforge_GENERATOR_PRIMARY'); ?></span>
                    </label>
                </div>
                <p class="curseforge-toggle-hint"><?php echo Text::_('MOD_curseforge_GENERATOR_PRIMARY_HINT'); ?></p>
            </div>

            <!-- Chart Preview -->
            <div class="curseforge-preview-area" id="curseforge-chart-preview-area">
                <div class="curseforge-preview-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW_LABEL'); ?></div>
                <div class="curseforge-preview-placeholder" id="curseforge-chart-placeholder">
                    <svg class="curseforge-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <span><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></span>
                </div>
                <div class="curseforge-preview-content" id="curseforge-chart-content" style="display:none;">
                    <img class="curseforge-preview-img" id="curseforge-chart-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW'); ?>" />
                </div>
            </div>

            <!-- Chart Output -->
            <div class="curseforge-output-group">
                <div class="curseforge-output-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_URL_LABEL'); ?></div>
                <div class="curseforge-code-block">
                    <code id="curseforge-chart-url"></code>
                </div>
                <div class="curseforge-output-label"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></div>
                <div class="curseforge-code-block">
                    <code id="curseforge-chart-md"></code>
                </div>
                <div class="curseforge-actions">
                    <button type="button" class="curseforge-btn curseforge-btn-primary" data-copy="curseforge-chart-md">
                        <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_CHART_MD'); ?>
                    </button>
                    <button type="button" class="curseforge-btn curseforge-btn-outline" data-copy="curseforge-chart-url">
                        <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_CHART_URL'); ?>
                    </button>
                </div>
            </div>
        </div>

        <p class="curseforge-status" id="curseforge-status" role="status" aria-live="polite"></p>
    </div>
</div>
