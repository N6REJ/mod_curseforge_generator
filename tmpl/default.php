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

use Joomla\CMS\Language\Text;

// Metric map (single source of truth) and the first metric used as default
$cfMetrics     = ModCurseforgeGeneratorHelper::METRICS;
$cfFirstMetric = reset($cfMetrics);

?>

<div class="mod-curseforge-generator <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')); ?>">
    <div class="container-fluid py-3" id="curseforge-root">
        <p class="text-muted small mb-3">
            <?php echo Text::_('MOD_curseforge_GENERATOR_INTRO'); ?>
            <a href="https://github.com/N6REJ/curseforge-downloads-action" target="_blank" rel="noopener">curseforge-downloads-action</a>.
        </p>

        <!-- Generator Lab Header -->
        <div class="mb-3">
            <h2 class="h4 fw-bold text-body"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_TITLE'); ?></h2>
            <p class="text-body-secondary small"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_DESC'); ?></p>
        </div>

        <!-- Tabs: Badge / Chart -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active nav-tab-btn bg-primary text-white" role="tab" data-tab="badge" aria-selected="true">
                    <?php echo Text::_('MOD_curseforge_GENERATOR_TAB_BADGE'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link nav-tab-btn bg-info text-white" role="tab" data-tab="chart" aria-selected="false">
                    <?php echo Text::_('MOD_curseforge_GENERATOR_TAB_CHART'); ?>
                </button>
            </li>
        </ul>

        <form id="curseforge-form" autocomplete="off">
            <!-- Repository Fields -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 fw-semibold"><?php echo Text::_('MOD_curseforge_GENERATOR_REPO_DETAILS'); ?></h3>
                    <p class="text-muted small mb-3"><?php echo Text::_('MOD_curseforge_GENERATOR_REPO_DETAILS_DESC'); ?></p>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="curseforge-owner" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_OWNER'); ?></label>
                            <input type="text" class="form-control" id="curseforge-owner" value="<?php echo htmlspecialchars($defaultOwner); ?>" spellcheck="false" />
                        </div>
                        <div class="col-md-3">
                            <label for="curseforge-repo" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_REPO'); ?></label>
                            <input type="text" class="form-control" id="curseforge-repo" value="<?php echo htmlspecialchars($defaultRepo); ?>" spellcheck="false" />
                        </div>
                        <div class="col-md-3">
                            <label for="curseforge-branch" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_BRANCH'); ?></label>
                            <input type="text" class="form-control" id="curseforge-branch" value="<?php echo htmlspecialchars($defaultBranch); ?>" spellcheck="false" />
                        </div>
                        <div class="col-md-3">
                            <label for="curseforge-provider" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_PROVIDER'); ?></label>
                            <select class="form-select" id="curseforge-provider">
                                <option value="raw" selected><?php echo Text::_('MOD_curseforge_GENERATOR_PROVIDER_RAW'); ?></option>
                                <option value="jsdelivr"><?php echo Text::_('MOD_curseforge_GENERATOR_PROVIDER_JSDELIVR'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Badge Tab Panel -->
        <div class="tab-panel show" id="curseforge-panel-badge" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 fw-semibold"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_BADGE'); ?></h3>
                    <p class="text-muted small mb-3"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_BADGE_DESC'); ?></p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="curseforge-metric" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_METRIC'); ?></label>
                            <select class="form-select" id="curseforge-metric">
                                <?php foreach ($cfMetrics as $metricKey => $metric): ?>
                                    <option value="<?php echo htmlspecialchars($metricKey . '|' . $metric['query'] . '|' . $metric['color'] . '|' . $metric['label'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('MOD_curseforge_GENERATOR_' . strtoupper($metricKey)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-label" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_LABEL'); ?></label>
                            <input type="text" class="form-control" id="curseforge-label" value="<?php echo htmlspecialchars($cfFirstMetric['label'], ENT_QUOTES, 'UTF-8'); ?>" data-user-edited="false" spellcheck="false" />
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-style" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE'); ?></label>
                            <select class="form-select" id="curseforge-style">
                                <option value="flat"><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE_FLAT'); ?></option>
                                <option value="flat-square" selected><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE_FLAT_SQUARE'); ?></option>
                                <option value="plastic"><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE_PLASTIC'); ?></option>
                                <option value="for-the-badge"><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE_FOR_THE_BADGE'); ?></option>
                                <option value="social"><?php echo Text::_('MOD_curseforge_GENERATOR_STYLE_SOCIAL'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="curseforge-color" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_COLOR'); ?></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="curseforge-color-picker" value="#<?php echo $cfFirstMetric['color']; ?>" title="Choose color" />
                                <input type="text" class="form-control" id="curseforge-color" value="<?php echo $cfFirstMetric['color']; ?>" spellcheck="false" maxlength="6" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-label-color" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_LABEL_COLOR'); ?></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="curseforge-label-color-picker" value="#<?php echo ModCurseforgeGeneratorHelper::DEFAULT_LABEL_COLOR; ?>" title="Choose label color" />
                                <input type="text" class="form-control" id="curseforge-label-color" value="<?php echo ModCurseforgeGeneratorHelper::DEFAULT_LABEL_COLOR; ?>" spellcheck="false" maxlength="6" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-logo" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_LOGO'); ?></label>
                            <select class="form-select" id="curseforge-logo">
                                <option value=""><?php echo Text::_('MOD_curseforge_GENERATOR_LOGO_NONE'); ?></option>
                                <option value="curseforge"><?php echo Text::_('MOD_curseforge_GENERATOR_LOGO_CURSEFORGE'); ?></option>
                                <option value="github" selected><?php echo Text::_('MOD_curseforge_GENERATOR_LOGO_GITHUB'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="curseforge-cache" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CACHE'); ?></label>
                            <input type="number" class="form-control" id="curseforge-cache" min="0" max="86400" step="1" value="<?php echo (int) ModCurseforgeGeneratorHelper::DEFAULT_CACHE_SECONDS; ?>" />
                            <div class="form-text"><?php echo Text::_('MOD_curseforge_GENERATOR_CACHE_DESC'); ?></div>
                        </div>
                    </div>

                    <!-- Badge Preview -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW_LABEL'); ?></small>
                        </div>
                        <div class="card-body text-center py-4" id="curseforge-badge-placeholder">
                            <svg class="text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h12"/></svg>
                            <div class="text-muted small" id="curseforge-badge-status" data-error-text="<?php echo Text::_('MOD_curseforge_GENERATOR_PREVIEW_UNAVAILABLE'); ?>"><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></div>
                        </div>
                        <div class="card-body text-center d-none" id="curseforge-badge-content">
                            <img class="img-fluid" id="curseforge-badge-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW'); ?>" />
                        </div>
                    </div>

                    <!-- Badge Output -->
                    <div class="mb-3">
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_URL_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-url" class="small text-body text-break"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-md" class="small text-body text-break"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_SHIELDS_URL_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-shields-url" class="small text-body text-break"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_SHIELDS_MD_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-shields-md" class="small text-body text-break"></code>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-copy="curseforge-badge-md">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_MD'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="curseforge-badge-url">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_URL'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="curseforge-badge-shields-md">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_SHIELDS_MD'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="curseforge-badge-shields-url">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_SHIELDS_URL'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Tab Panel -->
        <div class="tab-panel d-none" id="curseforge-panel-chart" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 fw-semibold"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_CHART'); ?></h3>
                    <p class="text-muted small mb-3"><?php echo Text::_('MOD_curseforge_GENERATOR_GEN_CHART_DESC'); ?></p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="curseforge-charttype" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTTYPE'); ?></label>
                            <select class="form-select" id="curseforge-charttype">
                                <option value="total-trend"><?php echo Text::_('MOD_curseforge_GENERATOR_TOTAL_TREND'); ?></option>
                                <option value="daily"><?php echo Text::_('MOD_curseforge_GENERATOR_DAILY'); ?></option>
                                <option value="weekly"><?php echo Text::_('MOD_curseforge_GENERATOR_WEEKLY'); ?></option>
                                <option value="monthly"><?php echo Text::_('MOD_curseforge_GENERATOR_MONTHLY'); ?></option>
                                <option value="yearly"><?php echo Text::_('MOD_curseforge_GENERATOR_YEARLY'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="curseforge-theme" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME'); ?></label>
                            <select class="form-select" id="curseforge-theme">
                                <option value="black"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_BLACK'); ?></option>
                                <option value="slate" selected><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_SLATE'); ?></option>
                                <option value="orange"><?php echo Text::_('MOD_curseforge_GENERATOR_THEME_ORANGE'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="curseforge-chart-width" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_WIDTH'); ?></label>
                            <input type="number" class="form-control" id="curseforge-chart-width" min="640" max="4096" step="10" value="1000" />
                        </div>
                        <div class="col-md-6">
                            <label for="curseforge-chart-height" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_HEIGHT'); ?></label>
                            <input type="number" class="form-control" id="curseforge-chart-height" min="240" max="2160" step="10" value="360" />
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="curseforge-chart-yticks" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_YTICKS'); ?></label>
                            <input type="number" class="form-control" id="curseforge-chart-yticks" min="2" max="12" step="1" value="6" />
                        </div>
                        <div class="col-md-6">
                            <label for="curseforge-chart-xlabels" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_XLABELDAYS'); ?></label>
                            <input type="number" class="form-control" id="curseforge-chart-xlabels" min="0" max="365" step="1" value="0" />
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="curseforge-chart-dateformat" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_DATELABELFORMAT'); ?></label>
                            <select class="form-select" id="curseforge-chart-dateformat">
                                <option value="yyyy-mm-dd" selected><?php echo Text::_('MOD_curseforge_GENERATOR_DATEFORMAT_YYYY_MM_DD'); ?></option>
                                <option value="yy/mm/dd"><?php echo Text::_('MOD_curseforge_GENERATOR_DATEFORMAT_YY_MM_DD'); ?></option>
                                <option value="dd/mm"><?php echo Text::_('MOD_curseforge_GENERATOR_DATEFORMAT_DD_MM'); ?></option>
                                <option value="mm/dd"><?php echo Text::_('MOD_curseforge_GENERATOR_DATEFORMAT_MM_DD'); ?></option>
                                <option value="none"><?php echo Text::_('MOD_curseforge_GENERATOR_DATEFORMAT_NONE'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="curseforge-chart-titlemode" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_TITLEMODE'); ?></label>
                            <select class="form-select" id="curseforge-chart-titlemode">
                                <option value="default" selected><?php echo Text::_('MOD_curseforge_GENERATOR_TITLEMODE_DEFAULT'); ?></option>
                                <option value="custom"><?php echo Text::_('MOD_curseforge_GENERATOR_TITLEMODE_CUSTOM'); ?></option>
                                <option value="none"><?php echo Text::_('MOD_curseforge_GENERATOR_TITLEMODE_NONE'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 d-none" id="curseforge-chart-titletext-wrap">
                            <label for="curseforge-chart-titletext" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CUSTOMTITLE'); ?></label>
                            <input type="text" class="form-control" id="curseforge-chart-titletext" spellcheck="false" />
                        </div>
                        <div class="col-md-6">
                            <label for="curseforge-chart-alt" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_ALTTEXT'); ?></label>
                            <input type="text" class="form-control" id="curseforge-chart-alt" spellcheck="false" />
                            <div class="form-text"><?php echo Text::_('MOD_curseforge_GENERATOR_ALTTEXT_DESC'); ?></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="curseforge-chart-zerobaseline" checked />
                                <label class="form-check-label" for="curseforge-chart-zerobaseline"><?php echo Text::_('MOD_curseforge_GENERATOR_ZEROBASELINE'); ?></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="curseforge-chart-showvalues" />
                                <label class="form-check-label" for="curseforge-chart-showvalues"><?php echo Text::_('MOD_curseforge_GENERATOR_SHOWVALUELABELS'); ?></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="curseforge-chart-showgenerated" checked />
                                <label class="form-check-label" for="curseforge-chart-showgenerated"><?php echo Text::_('MOD_curseforge_GENERATOR_SHOWGENERATEDAT'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="curseforge-chartdata" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTDATA'); ?></label>
                        <textarea class="form-control font-monospace" id="curseforge-chartdata" rows="6" spellcheck="false" placeholder="2026-10-01 1200&#10;2026-10-02 1250"></textarea>
                        <div class="form-text"><?php echo Text::_('MOD_curseforge_GENERATOR_CHARTDATA_DESC'); ?></div>
                    </div>

                    <!-- Chart Preview -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW_LABEL'); ?></small>
                        </div>
                        <div class="card-body text-center py-4" id="curseforge-chart-placeholder">
                            <svg class="text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                            <div class="text-muted small" id="curseforge-chart-status" data-error-text="<?php echo Text::_('MOD_curseforge_GENERATOR_PREVIEW_UNAVAILABLE'); ?>"><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></div>
                        </div>
                        <div class="card-body text-center d-none" id="curseforge-chart-content">
                            <img class="img-fluid" id="curseforge-chart-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW'); ?>" />
                        </div>
                    </div>

                    <!-- Chart Output -->
                    <div class="mb-3">
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_URL_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-chart-url" class="small text-body text-break"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></small>
                        <div class="bg-body-tertiary border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-chart-md" class="small text-body text-break"></code>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-copy="curseforge-chart-md">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_CHART_MD'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="curseforge-chart-url">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_CHART_URL'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-success mb-3 d-none" role="alert" id="curseforge-status-success"></div>
        <div class="alert alert-danger mb-3 d-none" role="alert" id="curseforge-status-error"></div>
    </div>
</div>
