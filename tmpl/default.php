<?php

/**
 * @package     mod_curseforge_generator
 * @subpackage  mod_curseforge_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// AJAX endpoint URL
$ajaxUrl = Route::_('index.php?format=json&xajax=1', false);

?>

<div class="mod-curseforge-generator <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')); ?>">
    <div class="container-fluid py-3" id="curseforge-root">
        <p class="text-muted small mb-3">
            <?php echo Text::_('MOD_curseforge_GENERATOR_INTRO'); ?>
            <a href="https://github.com/N6REJ/curseforge-downloads-action" target="_blank" rel="noopener">curseforge-downloads-action</a>.
        </p>

        <!-- Generator Lab Header -->
        <div class="mb-3">
            <h2 class="h4 fw-bold text-dark"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_TITLE'); ?></h2>
            <p class="text-body-secondary small"><?php echo Text::_('MOD_curseforge_GENERATOR_LAB_DESC'); ?></p>
        </div>

        <!-- Tabs: Badge / Chart -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active nav-tab-btn" role="tab" data-tab="badge" aria-selected="true">
                    <?php echo Text::_('MOD_curseforge_GENERATOR_TAB_BADGE'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link nav-tab-btn" role="tab" data-tab="chart" aria-selected="false">
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
                        <div class="col-md-4">
                            <label for="curseforge-owner" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_OWNER'); ?></label>
                            <input type="text" class="form-control" id="curseforge-owner" value="<?php echo htmlspecialchars($defaultOwner); ?>" spellcheck="false" />
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-repo" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_REPO'); ?></label>
                            <input type="text" class="form-control" id="curseforge-repo" value="<?php echo htmlspecialchars($defaultRepo); ?>" spellcheck="false" />
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-branch" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_BRANCH'); ?></label>
                            <input type="text" class="form-control" id="curseforge-branch" value="<?php echo htmlspecialchars($defaultBranch); ?>" spellcheck="false" />
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
                                <option value="total|$.stats.total|0A7EA4"><?php echo Text::_('MOD_curseforge_GENERATOR_TOTAL'); ?></option>
                                <option value="day|$.stats.day|1E8E3E"><?php echo Text::_('MOD_curseforge_GENERATOR_DAY'); ?></option>
                                <option value="week|$.stats.week|0069C2"><?php echo Text::_('MOD_curseforge_GENERATOR_WEEK'); ?></option>
                                <option value="month|$.stats.month|B26A00"><?php echo Text::_('MOD_curseforge_GENERATOR_MONTH'); ?></option>
                                <option value="year|$.stats.year|6A1B9A"><?php echo Text::_('MOD_curseforge_GENERATOR_YEAR'); ?></option>
                                <option value="profile|$.profile.defaultMode|5E35B1"><?php echo Text::_('MOD_curseforge_GENERATOR_PROFILE'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-label" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_LABEL'); ?></label>
                            <input type="text" class="form-control" id="curseforge-label" value="downloads/total" spellcheck="false" />
                        </div>
                        <div class="col-md-4">
                            <label for="curseforge-color" class="form-label"><?php echo Text::_('MOD_curseforge_GENERATOR_COLOR'); ?></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="curseforge-color-picker" value="#0A7EA4" title="Choose color" />
                                <input type="text" class="form-control" id="curseforge-color" value="0A7EA4" spellcheck="false" maxlength="6" />
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
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

                    <!-- Badge Preview -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW_LABEL'); ?></small>
                        </div>
                        <div class="card-body text-center py-4" id="curseforge-badge-placeholder">
                            <svg class="text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h12"/></svg>
                            <div class="text-muted small"><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></div>
                        </div>
                        <div class="card-body text-center d-none" id="curseforge-badge-content">
                            <img class="img-fluid" id="curseforge-badge-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_PREVIEW'); ?>" />
                        </div>
                    </div>

                    <!-- Badge Output -->
                    <div class="mb-3">
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_BADGE_URL_LABEL'); ?></small>
                        <div class="bg-light border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-url" class="small text-dark"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></small>
                        <div class="bg-light border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-badge-md" class="small text-dark"></code>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-copy="curseforge-badge-md">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_MD'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="curseforge-badge-url">
                                <?php echo Text::_('MOD_curseforge_GENERATOR_COPY_BADGE_URL'); ?>
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

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="curseforge-primary" />
                        <label class="form-check-label small text-body-secondary" for="curseforge-primary">
                            <?php echo Text::_('MOD_curseforge_GENERATOR_PRIMARY'); ?>
                        </label>
                    </div>
                    <p class="text-muted small mb-3"><?php echo Text::_('MOD_curseforge_GENERATOR_PRIMARY_HINT'); ?></p>

                    <!-- Chart Preview -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW_LABEL'); ?></small>
                        </div>
                        <div class="card-body text-center py-4" id="curseforge-chart-placeholder">
                            <svg class="text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                            <div class="text-muted small"><?php echo Text::_('MOD_curseforge_GENERATOR_WAITING'); ?></div>
                        </div>
                        <div class="card-body text-center d-none" id="curseforge-chart-content">
                            <img class="img-fluid" id="curseforge-chart-img" alt="<?php echo Text::_('MOD_curseforge_GENERATOR_CHART_PREVIEW'); ?>" />
                        </div>
                    </div>

                    <!-- Chart Output -->
                    <div class="mb-3">
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_CHART_URL_LABEL'); ?></small>
                        <div class="bg-light border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-chart-url" class="small text-dark"></code>
                        </div>
                        <small class="fw-semibold text-body-secondary"><?php echo Text::_('MOD_curseforge_GENERATOR_MARKDOWN_LABEL'); ?></small>
                        <div class="bg-light border rounded p-2 mb-2 overflow-auto">
                            <code id="curseforge-chart-md" class="small text-dark"></code>
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
