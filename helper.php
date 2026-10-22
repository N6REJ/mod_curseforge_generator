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
     * @param   string  $owner      GitHub owner
     * @param   string  $repo       Repository name
     * @param   string  $chartType  Chart type
     * @param   string  $theme      Chart theme
     * @param   string  $chartData  Date and count lines
     * @param   string  $color      Hex color (no #)
     *
     * @return  string
     */
    public static function buildChartUrl($owner, $repo, $chartType, $theme, $chartData, $color)
    {
        return Uri::root() . 'index.php?option=com_ajax&module=curseforge_generator&method=chart&format=raw'
            . '&owner=' . urlencode($owner)
            . '&repo=' . urlencode($repo)
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
     * @param   array   $labels  Labels in order
     * @param   array   $values  Numeric values
     * @param   string  $theme   black, slate or orange
     * @param   string  $color   Hex color (no #) for the line and latest value
     * @param   string  $title   Chart title
     *
     * @return  string
     */
    public static function buildChartSvg(array $labels, array $values, $theme, $color, $title)
    {
        $palettes = [
            'black'  => ['background' => '#0b0b0d', 'grid' => '#1f2937', 'axis' => '#94a3b8', 'text' => '#f8fafc', 'sub' => '#cbd5e1'],
            'slate'  => ['background' => '#0f172a', 'grid' => '#1e293b', 'axis' => '#94a3b8', 'text' => '#f8fafc', 'sub' => '#cbd5e1'],
            'orange' => ['background' => '#1c0f05', 'grid' => '#3b2212', 'axis' => '#d6a57a', 'text' => '#fff7ed', 'sub' => '#fed7aa'],
        ];
        $palette = $palettes[$theme] ?? $palettes['slate'];

        $width  = 1000;
        $height = 360;
        $left   = 64;
        $right  = 976;
        $top    = 52;
        $bottom = 304;
        $innerW = $right - $left;
        $innerH = $bottom - $top;
        $count  = count($values);
        $maxV   = $count ? max(max($values), 1) : 1;
        $stroke = '#' . $color;
        $title  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . $title . '">'
            . '<defs><linearGradient id="fill" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0%" stop-color="' . $stroke . '" stop-opacity="0.32"/>'
            . '<stop offset="100%" stop-color="' . $stroke . '" stop-opacity="0.05"/>'
            . '</linearGradient></defs>'
            . '<rect width="' . $width . '" height="' . $height . '" rx="16" fill="' . $palette['background'] . '"/>'
            . '<text x="' . $left . '" y="34" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="' . $palette['text'] . '">' . $title . '</text>';

        for ($j = 0; $j <= 6; $j++) {
            $y     = $top + $j * $innerH / 6;
            $value = (int) round($maxV * (6 - $j) / 6);
            $svg  .= '<line x1="' . $left . '" y1="' . $y . '" x2="' . $right . '" y2="' . $y . '" stroke="' . $palette['grid'] . '" stroke-width="1"/>'
                . '<text x="56" y="' . ($y + 4) . '" text-anchor="end" font-family="Arial, sans-serif" font-size="12" fill="' . $palette['axis'] . '">' . $value . '</text>';
        }

        if ($count) {
            $points = [];

            foreach ($values as $i => $value) {
                $x = $count > 1 ? $left + $i * $innerW / ($count - 1) : $left + $innerW / 2;
                $y = $bottom - ($value / $maxV) * $innerH;
                $points[] = [round($x, 2), round($y, 2)];
            }

            $first = $points[0];
            $last  = $points[$count - 1];
            $area  = 'M ' . $first[0] . ' ' . $bottom;
            $line  = [];

            foreach ($points as $p) {
                $area .= ' L ' . $p[0] . ' ' . $p[1];
                $line[] = $p[0] . ',' . $p[1];
            }

            $area .= ' L ' . $last[0] . ' ' . $bottom . ' Z';

            $svg .= '<path d="' . $area . '" fill="url(#fill)"/>'
                . '<polyline points="' . implode(' ', $line) . '" fill="none" stroke="' . $stroke . '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'
                . '<circle cx="' . $last[0] . '" cy="' . $last[1] . '" r="4.5" fill="' . $stroke . '"/>';

            $step  = max(1, (int) ceil(($count - 1) / 6));
            $ticks = [];

            for ($i = 0; $i < $count; $i += $step) {
                $ticks[] = $i;
            }

            if (end($ticks) !== $count - 1) {
                $ticks[] = $count - 1;
            }

            foreach ($ticks as $i) {
                $anchor = $i === 0 ? 'start' : ($i === $count - 1 ? 'end' : 'middle');
                $svg   .= '<text x="' . $points[$i][0] . '" y="336" text-anchor="' . $anchor . '" font-family="Arial, sans-serif" font-size="12" fill="' . $palette['axis'] . '">' . htmlspecialchars((string) $labels[$i], ENT_QUOTES, 'UTF-8') . '</text>';
            }

            $svg .= '<text x="' . $right . '" y="34" text-anchor="end" font-family="Arial, sans-serif" font-size="14" font-weight="700" fill="' . $palette['text'] . '">Latest total: ' . (int) $values[$count - 1] . '</text>';
        } else {
            $svg .= '<text x="' . ($width / 2) . '" y="' . ($height / 2) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="' . $palette['sub'] . '">No data</text>';
        }

        $svg .= '<text x="' . $left . '" y="352" font-family="Arial, sans-serif" font-size="13" fill="' . $palette['sub'] . '">Generated ' . date('Y-m-d') . '</text>';

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
        $chartUrl = self::buildChartUrl($owner, $repo, $chartType, $theme, $chartData, $color);
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
        $owner     = self::sanitizeSlug($input->getString('owner', ''), 'OWNER');
        $repo      = self::sanitizeSlug($input->getString('repo', ''), 'REPOSITORY');

        [$labels, $values] = self::aggregateChartData(self::parseChartData($chartData), $chartType);

        $app->setHeader('Content-Type', 'image/svg+xml; charset=utf-8', true);

        return self::buildChartSvg($labels, $values, $theme, $color, $owner . '/' . $repo . ' release downloads');
    }
}
