<?php

/**
 * @package     mod_curseforge_generator
 * @subpackage  mod_curseforge_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModcurseforgeGeneratorHelper
{
    private const SAFE_SLUG  = '/^[A-Za-z0-9_.-]+$/';
    private const SAFE_PATH  = '/^[A-Za-z0-9_.\-\/]+$/';
    private const SAFE_COLOR = '/^[0-9A-Fa-f]{6}$/';

    /**
     * Resolve the GitHub organisation/user that owns the repositories.
     * Accepts "https://github.com/N6REJ", "github.com/N6REJ", "N6REJ" or
     * "N6REJ/mod_curseforge_generator" (only the first path segment is used).
     *
     * @param   \Joomla\Registry\Registry  $params
     *
     * @return  string
     */
    public static function getOwner($params)
    {
        $owner = trim((string) $params->get('repo_owner'));
        $owner = preg_replace('#^(?:https?://)?(?:www\.)?github\.com/#i', '', $owner);
        $owner = explode('/', trim($owner, '/ '))[0];

        return $owner !== '' ? $owner : 'N6REJ';
    }

    /**
     * Build the raw GitHub URL for a badge or chart image.
     *
     * @param   string  $owner   GitHub owner
     * @param   string  $repo    Repository name
     * @param   string  $branch  Git branch
     * @param   string  $path    File path within the repository
     *
     * @return  string
     */
    public static function getRawUrl($owner, $repo, $branch, $path)
    {
        return sprintf(
            'https://raw.githubusercontent.com/%s/%s/%s/%s',
            rawurlencode($owner),
            rawurlencode($repo),
            rawurlencode($branch),
            rawurlencode($path)
        );
    }

    /**
     * Sanitize a slug value (owner, repo, branch).
     *
     * @param   string  $text     Raw input
     * @param   string  $fallback Default if invalid
     *
     * @return  string
     */
    public static function sanitizeSlug($text, $fallback)
    {
        return preg_match(self::SAFE_SLUG, $text) ? $text : $fallback;
    }

    /**
     * Sanitize a file path value.
     *
     * @param   string  $text     Raw input
     * @param   string  $fallback Default if invalid
     *
     * @return  string
     */
    public static function sanitizePath($text, $fallback)
    {
        $normalized = str_replace('\\', '/', $text);
        $normalized = ltrim($normalized, '/');

        if (strpos($normalized, '..') !== false) {
            return $fallback;
        }

        return preg_match(self::SAFE_PATH, $normalized) ? $normalized : $fallback;
    }

    /**
     * Sanitize a hex color value.
     *
     * @param   string  $text     Raw input
     * @param   string  $fallback Default if invalid
     *
     * @return  string
     */
    public static function sanitizeColor($text, $fallback)
    {
        return preg_match(self::SAFE_COLOR, $text) ? strtoupper($text) : $fallback;
    }

    /**
     * Build a Shields.io dynamic JSON badge URL.
     *
     * @param   string  $owner    GitHub owner
     * @param   string  $repo     Repository name
     * @param   string  $branch   Git branch
     * @param   string  $jsonPath Path to JSON file in the repository
     * @param   string  $query    JSONPath query
     * @param   string  $label    Badge label text
     * @param   string  $color    Hex color (no #)
     * @param   string  $style    Badge style (flat, flat-square, plastic, for-the-badge, social)
     *
     * @return  string
     */
    public static function buildBadgeUrl($owner, $repo, $branch, $jsonPath, $query, $label, $color, $style = 'flat-square')
    {
        $source = self::getRawUrl($owner, $repo, $branch, $jsonPath);

        // Validate style against allowed values
        $allowedStyles = ['flat', 'flat-square', 'plastic', 'for-the-badge', 'social'];
        if (!in_array($style, $allowedStyles, true)) {
            $style = 'flat-square';
        }

        return 'https://img.shields.io/badge/dynamic/json'
            . '?url=' . urlencode($source)
            . '&query=' . urlencode($query)
            . '&label=' . urlencode($label)
            . '&color=' . $color
            . '&style=' . $style
            . '&logo=curseforge'
            . '&cacheSeconds=900';
    }

    /**
     * Build a chart image URL from the repository.
     *
     * @param   string  $owner      GitHub owner
     * @param   string  $repo       Repository name
     * @param   string  $branch     Git branch
     * @param   string  $chartPath  Path to chart SVG in the repository
     *
     * @return  string
     */
    public static function buildChartUrl($owner, $repo, $branch, $chartPath)
    {
        return self::getRawUrl($owner, $repo, $branch, $chartPath);
    }

    /**
     * Handle the AJAX request and return a JSON response with generated badge/chart data.
     *
     * @return  void
     */
    public static function ajaxGenerate()
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $post     = $input->post;
        $owner    = $post->get('owner', '', 'STRING');
        $repo     = $post->get('repo', '', 'STRING');
        $branch   = $post->get('branch', '', 'STRING');
        $jsonPath = $post->get('jsonpath', 'curseforge/downloads.json', 'STRING');
        $chartPath = $post->get('chartpath', 'curseforge/downloads-trend.svg', 'STRING');
        $chartDir = $post->get('chartdir', 'curseforge/charts', 'STRING');

        // Badge fields
        $metric = $post->get('metric', 'total|$.stats.total|0A7EA4', 'STRING');
        $label  = $post->get('label', 'downloads/total', 'STRING');
        $color  = $post->get('color', '0A7EA4', 'STRING');
        $style  = $post->get('style', 'flat-square', 'STRING');

        // Chart fields
        $chartType  = $post->get('charttype', 'total-trend', 'STRING');
        $theme      = $post->get('theme', 'slate', 'STRING');
        $usePrimary = (bool) $post->get('primary', false, 'BOOLEAN');

        // Sanitize
        $owner     = self::sanitizeSlug($owner, 'OWNER');
        $repo      = self::sanitizeSlug($repo, 'REPOSITORY');
        $branch    = self::sanitizeSlug($branch, 'stats-data');
        $jsonPath  = self::sanitizePath($jsonPath, 'curseforge/downloads.json');
        $chartPath = self::sanitizePath($chartPath, 'curseforge/downloads-trend.svg');
        $chartDir  = self::sanitizePath($chartDir, 'curseforge/charts');

        // Parse metric
        $metricParts  = explode('|', $metric);
        $defaultQuery = $metricParts[1] ?? '$.stats.total';
        $defaultColor = $metricParts[2] ?? '0A7EA4';
        $query        = $defaultQuery;
        $color        = self::sanitizeColor($color, $defaultColor);
        $label        = $label ?: ('downloads/' . ($metricParts[0] ?? 'total'));

        // Build badge
        $badgeUrl = self::buildBadgeUrl($owner, $repo, $branch, $jsonPath, $query, $label, $color, $style);
        $badgeMd  = '!' . $label . '(' . $badgeUrl . ')';

        // Build chart
        if ($usePrimary) {
            $chartTarget = $chartPath;
        } else {
            $chartTarget = $chartDir . '/' . $chartType . '--' . $theme . '.svg';
        }

        $chartUrl = self::buildChartUrl($owner, $repo, $branch, $chartTarget);
        $chartMd  = '![Downloads ' . $chartType . ' ' . $theme . '](' . $chartUrl . ')';

        // Return JSON response
        $response = [
            'success' => true,
            'badge'   => [
                'url'   => $badgeUrl,
                'md'    => $badgeMd,
                'label' => $label,
            ],
            'chart'   => [
                'url'   => $chartUrl,
                'md'    => $chartMd,
                'type'  => $chartType,
                'theme' => $theme,
            ],
        ];

        $app->setHeader('Content-Type', 'application/json')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Pragma', 'no-cache');

        echo json_encode($response);
        $app->close();
    }
}
