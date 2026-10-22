<?php

/**
 * @package     mod_curseforge_generator
 * @subpackage  mod_curseforge_generator
 * @license     MIT License
 * @link        https://github.com/N6REJ/curseforge-downloads-action
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class ModCurseforgeGeneratorHelper
{
    private const SAFE_SLUG  = '/^[A-Za-z0-9_.-]+$/';
    private const SAFE_PATH  = '/^[A-Za-z0-9_.\-\/]+$/';
    private const SAFE_COLOR = '/^[0-9A-Fa-f]{6}$/';

    /**
     * Build the raw GitHub URL for a JSON data file.
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
     * Keep a value only if it is one of the allowed choices.
     *
     * @param   string  $text     Raw input
     * @param   array   $allowed  Allowed values
     * @param   string  $fallback Default if invalid
     *
     * @return  string
     */
    public static function sanitizeChoice($text, array $allowed, $fallback)
    {
        return in_array($text, $allowed, true) ? $text : $fallback;
    }

    /**
     * Build the module endpoint URL that renders the chart SVG.
     *
     * @param   string  $chartType  Chart type
     * @param   string  $theme      Chart theme
     * @param   string  $chartData  Date and count lines
     *
     * @return  string
     */
    public static function buildChartUrl($chartType, $theme, $chartData, $color)
    {
        return Uri::root() . 'index.php?option=com_ajax&module=curseforge_generator&method=chart&format=raw'
            . '&charttype=' . urlencode($chartType)
            . '&theme=' . urlencode($theme)
            . '&color=' . urlencode($color)
            . '&chartdata=' . urlencode($chartData);
    }

    /**
     * Parse date and count lines (YYYY-MM-DD count) into a sorted map.
     *
     * @param   string  $text  Raw multi-line input
     *
     * @return  array
     */
    public static function parseChartData($text)
    {
        $points = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            if (preg_match('/^\s*(\d{4}-\d{2}-\d{2})[\s,;]+(\d+)\s*$/', $line, $matches)) {
                $points[$matches[1]] = (int) $matches[2];
            }
        }

        ksort($points);

        return $points;
    }

    /**
     * Turn the totals into the labels and values for the chosen chart type.
     *
     * @param   array   $points     Map of date to total
     * @param   string  $chartType  total-trend, daily, weekly, monthly or yearly
     *
     * @return  array  Two arrays: labels and values
     */
    public static function aggregateChartData(array $points, $chartType)
    {
        $labels = array_keys($points);
        $values = array_values($points);

        if ($chartType === 'total-trend') {
            return [$labels, $values];
        }

        $deltas = [];

        for ($i = 1; $i < count($values); $i++) {
            $deltas[$labels[$i]] = $values[$i] - $values[$i - 1];
        }

        if ($chartType === 'daily') {
            return [array_keys($deltas), array_values($deltas)];
        }

        $groups = [];

        if ($chartType === 'weekly') {
            foreach (array_chunk($deltas, 7, true) as $chunk) {
                $groups[array_key_first($chunk)] = array_sum($chunk);
            }
        } else {
            $length = $chartType === 'monthly' ? 7 : 4;

            foreach ($deltas as $date => $delta) {
                $key = substr($date, 0, $length);
                $groups[$key] = ($groups[$key] ?? 0) + $delta;
            }
        }

        return [array_keys($groups), array_values($groups)];
    }

    /**
     * Build an SVG line chart from labels and values.
     *
     * @param   array   $labels     Labels in order
     * @param   array   $values     Numeric values
     * @param   string  $chartType  Chart type, used in the title
     * @param   string  $theme      black, slate or orange
     *
     * @return  string
     */
    public static function buildChartSvg(array $labels, array $values, $chartType, $theme, $color)
    {
        $palettes = [
            'black'  => ['background' => '#111111', 'text' => '#cccccc'],
            'slate'  => ['background' => '#334155', 'text' => '#e2e8f0'],
            'orange' => ['background' => '#7c2d12', 'text' => '#ffedd5'],
        ];
        $palette = $palettes[$theme] ?? $palettes['slate'];

        $width  = 600;
        $height = 300;
        $pad    = 40;
        $innerW = $width - 2 * $pad;
        $innerH = $height - 2 * $pad;
        $count  = count($values);
        $maxV   = $count ? max(max($values), 1) : 1;

        $coords = [];

        foreach ($values as $i => $value) {
            $x = $count > 1 ? $pad + $i * $innerW / ($count - 1) : $pad + $innerW / 2;
            $y = $pad + $innerH - ($value / $maxV) * $innerH;
            $coords[] = round($x, 1) . ',' . round($y, 1);
        }

        $title = htmlspecialchars('Downloads ' . $chartType, ENT_QUOTES, 'UTF-8');
        $text  = 'fill="' . $palette['text'] . '" font-family="sans-serif"';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">'
            . '<rect width="100%" height="100%" fill="' . $palette['background'] . '"/>'
            . '<text x="' . $pad . '" y="25" ' . $text . ' font-size="14">' . $title . '</text>';

        if ($count) {
            $svg .= '<polyline fill="none" stroke="#' . $color . '" stroke-width="2" points="' . implode(' ', $coords) . '"/>';
            $svg .= '<text x="' . $pad . '" y="' . ($height - 10) . '" ' . $text . ' font-size="11">' . htmlspecialchars((string) $labels[0], ENT_QUOTES, 'UTF-8') . '</text>';
            $svg .= '<text x="' . ($width - $pad) . '" y="' . ($height - 10) . '" text-anchor="end" ' . $text . ' font-size="11">' . htmlspecialchars((string) $labels[$count - 1], ENT_QUOTES, 'UTF-8') . '</text>';
            $svg .= '<text x="' . ($width - $pad) . '" y="25" text-anchor="end" fill="#' . $color . '" font-family="sans-serif" font-size="14">' . (int) $values[$count - 1] . '</text>';
        } else {
            $svg .= '<text x="' . ($width / 2) . '" y="' . ($height / 2) . '" text-anchor="middle" ' . $text . ' font-size="14">No data</text>';
        }

        return $svg . '</svg>';
    }

    /**
     * Handle the com_ajax request and return a JSON string with generated badge/chart data.
     *
     * @return  string
     */
    public static function getAjax()
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $post   = $input->post;
        $owner  = $post->get('owner', '', 'STRING');
        $repo   = $post->get('repo', '', 'STRING');
        $branch = $post->get('branch', '', 'STRING');
        $jsonPath = $post->get('jsonpath', 'curseforge/downloads.json', 'STRING');

        // Badge fields
        $metric = $post->get('metric', 'total|$.stats.total|0A7EA4', 'STRING');
        $label  = $post->get('label', 'downloads/total', 'STRING');
        $color  = $post->get('color', '0A7EA4', 'STRING');
        $style  = $post->get('style', 'flat-square', 'STRING');

        // Chart fields
        $chartType = self::sanitizeChoice($post->get('charttype', 'total-trend', 'STRING'), ['total-trend', 'daily', 'weekly', 'monthly', 'yearly'], 'total-trend');
        $theme     = self::sanitizeChoice($post->get('theme', 'slate', 'STRING'), ['black', 'slate', 'orange'], 'slate');
        $chartData = $post->get('chartdata', '', 'RAW');

        // Sanitize
        $owner    = self::sanitizeSlug($owner, 'OWNER');
        $repo     = self::sanitizeSlug($repo, 'REPOSITORY');
        $branch   = self::sanitizeSlug($branch, 'stats-data');
        $jsonPath = self::sanitizePath($jsonPath, 'curseforge/downloads.json');

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
        $chartUrl = self::buildChartUrl($chartType, $theme, $chartData, $color);
        $chartMd  = '![Downloads ' . $chartType . ' ' . $theme . '](' . $chartUrl . ')';

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

        return json_encode($response);
    }

    /**
     * Render the chart SVG for the com_ajax chart method.
     *
     * @return  string
     */
    public static function chartAjax()
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $chartType = self::sanitizeChoice($input->getCmd('charttype', 'total-trend'), ['total-trend', 'daily', 'weekly', 'monthly', 'yearly'], 'total-trend');
        $theme     = self::sanitizeChoice($input->getCmd('theme', 'slate'), ['black', 'slate', 'orange'], 'slate');
        $chartData = $input->getString('chartdata', '');
        $color     = self::sanitizeColor($input->getString('color', ''), '0A7EA4');

        [$labels, $values] = self::aggregateChartData(self::parseChartData($chartData), $chartType);

        $app->setHeader('Content-Type', 'image/svg+xml; charset=utf-8', true);

        return self::buildChartSvg($labels, $values, $chartType, $theme, $color);
    }
}
