<?php

/**
 * @package     N6REJ.Module.CFDGGenerator
 * @subpackage  mod_cfdg_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Load Web Assets
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_cfdg_generator.style', 'mod_cfdg_generator/style.css');
$wa->registerAndUseScript('mod_cfdg_generator.script', 'mod_cfdg_generator/cfdg-generator.js', [], ['defer' => true], ['core']);

// Pass default values to JS
$doc = Factory::getDocument();
$doc->addScriptOptions('mod_cfdg_generator', [
    'defaultOwner' => $defaultOwner,
    'defaultRepo'  => $defaultRepo,
    'defaultBranch' => $defaultBranch,
]);
?>

<div class="mod-cfdg-generator <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')); ?>">
    <div class="cfdg" id="cfdg-root">
        <p class="cfdg-intro text-muted">
            <?php echo Text::_('MOD_CFDG_GENERATOR_INTRO'); ?>
            <a href="https://github.com/N6REJ/curseforge-downloads-action" target="_blank" rel="noopener">curseforge-downloads-action</a>.
        </p>

        <form class="cfdg-form" id="cfdg-form" autocomplete="off">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="cfdg-owner" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_OWNER'); ?></label>
                    <input type="text" class="form-control" id="cfdg-owner" value="<?php echo htmlspecialchars($defaultOwner); ?>" spellcheck="false" />
                </div>
                <div class="col-md-4">
                    <label for="cfdg-repo" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_REPO'); ?></label>
                    <input type="text" class="form-control" id="cfdg-repo" value="<?php echo htmlspecialchars($defaultRepo); ?>" spellcheck="false" />
                </div>
                <div class="col-md-4">
                    <label for="cfdg-branch" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_BRANCH'); ?></label>
                    <input type="text" class="form-control" id="cfdg-branch" value="<?php echo htmlspecialchars($defaultBranch); ?>" spellcheck="false" />
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="cfdg-jsonpath" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_JSONPATH'); ?></label>
                    <input type="text" class="form-control" id="cfdg-jsonpath" value="curseforge/downloads.json" spellcheck="false" />
                </div>
                <div class="col-md-4">
                    <label for="cfdg-chartpath" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_CHARTPATH'); ?></label>
                    <input type="text" class="form-control" id="cfdg-chartpath" value="curseforge/downloads-trend.svg" spellcheck="false" />
                </div>
                <div class="col-md-4">
                    <label for="cfdg-chartdir" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_CHARTDIR'); ?></label>
                    <input type="text" class="form-control" id="cfdg-chartdir" value="curseforge/charts" spellcheck="false" />
                </div>
            </div>

            <fieldset class="card p-3 mb-3">
                <legend class="px-2 fw-semibold text-secondary"><?php echo Text::_('MOD_CFDG_GENERATOR_BADGE'); ?></legend>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="cfdg-metric" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_METRIC'); ?></label>
                        <select class="form-select" id="cfdg-metric">
                            <option value="total|$.stats.total|0A7EA4"><?php echo Text::_('MOD_CFDG_GENERATOR_TOTAL'); ?></option>
                            <option value="day|$.stats.day|1E8E3E"><?php echo Text::_('MOD_CFDG_GENERATOR_DAY'); ?></option>
                            <option value="week|$.stats.week|0069C2"><?php echo Text::_('MOD_CFDG_GENERATOR_WEEK'); ?></option>
                            <option value="month|$.stats.month|B26A00"><?php echo Text::_('MOD_CFDG_GENERATOR_MONTH'); ?></option>
                            <option value="year|$.stats.year|6A1B9A"><?php echo Text::_('MOD_CFDG_GENERATOR_YEAR'); ?></option>
                            <option value="profile|$.profile.defaultMode|5E35B1"><?php echo Text::_('MOD_CFDG_GENERATOR_PROFILE'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cfdg-label" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_LABEL'); ?></label>
                        <input type="text" class="form-control" id="cfdg-label" value="downloads/total" spellcheck="false" />
                    </div>
                    <div class="col-md-4">
                        <label for="cfdg-color" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_COLOR'); ?></label>
                        <input type="text" class="form-control" id="cfdg-color" value="0A7EA4" spellcheck="false" maxlength="6" />
                    </div>
                </div>
            </fieldset>

            <fieldset class="card p-3 mb-3">
                <legend class="px-2 fw-semibold text-secondary"><?php echo Text::_('MOD_CFDG_GENERATOR_CHART'); ?></legend>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="cfdg-charttype" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_CHARTTYPE'); ?></label>
                        <select class="form-select" id="cfdg-charttype">
                            <option value="total-trend">total-trend</option>
                            <option value="daily">daily</option>
                            <option value="weekly">weekly</option>
                            <option value="monthly">monthly</option>
                            <option value="yearly">yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cfdg-theme" class="form-label"><?php echo Text::_('MOD_CFDG_GENERATOR_THEME'); ?></label>
                        <select class="form-select" id="cfdg-theme">
                            <option value="black">black</option>
                            <option value="slate" selected>slate</option>
                            <option value="orange">orange</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cfdg-primary" />
                            <label class="form-check-label text-muted" for="cfdg-primary"><?php echo Text::_('MOD_CFDG_GENERATOR_PRIMARY'); ?></label>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>

        <div class="cfdg-output">
            <h3 class="mt-4 mb-2"><?php echo Text::_('MOD_CFDG_GENERATOR_BADGE'); ?></h3>
            <pre class="cfdg-code"><code id="cfdg-badge-md"></code></pre>
            <div class="cfdg-actions d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-primary btn-sm" data-copy="cfdg-badge-md"><?php echo Text::_('MOD_CFDG_GENERATOR_COPY_BADGE_MD'); ?></button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-copy="cfdg-badge-url"><?php echo Text::_('MOD_CFDG_GENERATOR_COPY_BADGE_URL'); ?></button>
            </div>
            <pre class="cfdg-code cfdg-url"><code id="cfdg-badge-url"></code></pre>
            <div class="cfdg-preview mb-3">
                <div class="cfdg-preview-placeholder cfdg-badge-placeholder">
                    <svg class="cfdg-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h12"/></svg>
                    <span><?php echo Text::_('MOD_CFDG_GENERATOR_BADGE_PREVIEW'); ?></span>
                </div>
                <img class="img-fluid" id="cfdg-badge-img" alt="<?php echo Text::_('MOD_CFDG_GENERATOR_BADGE_PREVIEW'); ?>" />
            </div>

            <hr class="cfdg-divider" />

            <h3 class="mb-2"><?php echo Text::_('MOD_CFDG_GENERATOR_CHART'); ?></h3>
            <pre class="cfdg-code"><code id="cfdg-chart-md"></code></pre>
            <div class="cfdg-actions d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-primary btn-sm" data-copy="cfdg-chart-md"><?php echo Text::_('MOD_CFDG_GENERATOR_COPY_CHART_MD'); ?></button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-copy="cfdg-chart-url"><?php echo Text::_('MOD_CFDG_GENERATOR_COPY_CHART_URL'); ?></button>
            </div>
            <pre class="cfdg-code cfdg-url"><code id="cfdg-chart-url"></code></pre>
            <div class="cfdg-preview mb-3">
                <div class="cfdg-preview-placeholder cfdg-chart-placeholder">
                    <svg class="cfdg-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <span><?php echo Text::_('MOD_CFDG_GENERATOR_CHART_PREVIEW'); ?></span>
                </div>
                <img class="img-fluid" id="cfdg-chart-img" alt="<?php echo Text::_('MOD_CFDG_GENERATOR_CHART_PREVIEW'); ?>" />
            </div>
        </div>

        <p class="cfdg-status mb-0" id="cfdg-status" role="status" aria-live="polite"></p>
    </div>
</div>
