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

    public const PROVIDERS    = ['raw', 'jsdelivr'];
    public const BADGE_STYLES = ['flat', 'flat-square', 'plastic', 'for-the-badge', 'social'];
    public const LOGO_CHOICES = ['', 'curseforge'];

    public const METRICS = [
        'total'   => ['query' => '$.stats.total',         'color' => '0A7EA4'],
        'day'     => ['query' => '$.stats.day',           'color' => '1E8E3E'],
        'week'    => ['query' => '$.stats.week',          'color' => '0069C2'],
        'month'   => ['query' => '$.stats.month',         'color' => 'B26A00'],
        'year'    => ['query' => '$.stats.year',          'color' => '6A1B9A'],
        'profile' => ['query' => '$.profile.defaultMode', 'color' => '5E35B1'],
    ];

    public const DEFAULT_LABEL_COLOR = '555555';

    private const CURSEFORGE_LOGO_PATH = 'M18.326 9.2145S23.2261 8.4418 24 6.1882h-7.5066V4.4H0l2.0318 2.3576V9.173s5.1267-.2665 7.1098 1.2372c2.7146 2.516-3.053 5.917-3.053 5.917L5.0995 19.6c1.5465-1.4726 4.494-3.3775 9.8983-3.2857-2.0565.65-4.1245 1.6651-5.7344 3.2857h10.9248l-1.0288-3.2726s-7.918-4.6688-.8336-7.1127z';

    private const VERDANA_WIDTHS = [
        ' ' => 3.9, '!' => 4.7, '"' => 5.5, '#' => 8.2, '$' => 7.0, '%' => 10.4, '&' => 8.2, "'" => 3.1,
        '(' => 4.4, ')' => 4.4, '*' => 6.6, '+' => 7.0, ',' => 3.9, '-' => 4.5, '.' => 3.9, '/' => 4.4,
        '0' => 7.0, '1' => 7.0, '2' => 7.0, '3' => 7.0, '4' => 7.0, '5' => 7.0, '6' => 7.0, '7' => 7.0,
        '8' => 7.0, '9' => 7.0,
        ':' => 3.9, ';' => 3.9, '<' => 7.0, '=' => 7.6, '>' => 7.0, '?' => 6.2, '@' => 10.5,
        'A' => 7.8, 'B' => 7.6, 'C' => 7.7, 'D' => 8.4, 'E' => 6.8, 'F' => 6.4, 'G' => 8.5, 'H' => 8.3,
        'I' => 4.7, 'J' => 5.6, 'K' => 7.6, 'L' => 6.3, 'M' => 9.5, 'N' => 8.4, 'O' => 8.8, 'P' => 7.2,
        'Q' => 8.8, 'R' => 7.8, 'S' => 7.5, 'T' => 7.0, 'U' => 8.3, 'V' => 7.8, 'W' => 11.3, 'X' => 7.8,
        'Y' => 7.5, 'Z' => 7.2,
        '[' => 4.4, '\\' => 4.4, ']' => 4.4, '^' => 7.0, '_' => 7.0, '`' => 4.4,
        'a' => 6.6, 'b' => 7.0, 'c' => 5.9, 'd' => 7.0, 'e' => 6.6, 'f' => 4.4, 'g' => 7.0, 'h' => 7.0,
        'i' => 3.1, 'j' => 3.9, 'k' => 6.6, 'l' => 3.1, 'm' => 10.5, 'n' => 7.0, 'o' => 6.8, 'p' => 7.0,
        'q' => 7.0, 'r' => 4.9, 's' => 5.9, 't' => 4.6, 'u' => 7.0, 'v' => 6.6, 'w' => 9.5, 'x' => 6.6,
        'y' => 6.6, 'z' => 6.0,
        '{' => 5.5, '|' => 4.4, '}' => 5.5, '~' => 8.2,
    ];

    private static $fetchCache = [];

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
        $owner  = rawurlencode($owner);
        $repo   = rawurlencode($repo);
        $branch = rawurlencode($branch);

        return sprintf(
            'https://raw.githubusercontent.com/%s/%s/%s/%s',
            $owner,
            $repo,
            $branch,
            $path
        );
    }

    /**
     * Build the source URL for the published stats file based on provider.
     */
    public static function getSourceUrl($provider, $owner, $repo, $branch, $path)
    {
        $provider = self::sanitizeChoice($provider, self::PROVIDERS, 'raw');
        $owner    = rawurlencode($owner);
        $repo     = rawurlencode($repo);
        $branch   = rawurlencode($branch);
        $path     = ltrim(str_replace('\\', '/', $path), '/');

        if ($provider === 'jsdelivr') {
            return sprintf('https://cdn.jsdelivr.net/gh/%s/%s@%s/%s', $owner, $repo, $branch, $path);
        }

        return sprintf('https://raw.githubusercontent.com/%s/%s/%s/%s', $owner, $repo, $branch, $path);
    }

    /**
     * Sanitize a slug value (owner, repo, branch).
     */
    public static function sanitizeSlug($text, $fallback)
    {
        return preg_match(self::SAFE_SLUG, $text) ? $text : $fallback;
    }

    /**
     * Sanitize a file path value.
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
     */
    public static function sanitizeColor($text, $fallback)
    {
        $text = ltrim((string) $text, '#');
        return preg_match(self::SAFE_COLOR, $text) ? strtoupper($text) : $fallback;
    }

    /**
     * Keep a value only if it is one of the allowed choices.
     */
    public static function sanitizeChoice($text, array $allowed, $fallback)
    {
        return in_array((string) $text, $allowed, true) ? (string) $text : $fallback;
    }

    /**
     * Build a Shields.io dynamic JSON badge URL.
     */
    public static function buildBadgeUrl($owner, $repo, $branch, $jsonPath, $query, $label, $color, $style = 'flat-square', $labelColor = '', $logo = '', $cacheSeconds = 900, $provider = 'raw')
    {
        $source = self::getSourceUrl($provider, $owner, $repo, $branch, $jsonPath);
        $style  = self::sanitizeChoice($style, self::BADGE_STYLES, 'flat-square');
        $color  = self::sanitizeColor($color, '0A7EA4');
        $labelColor = $labelColor !== '' ? self::sanitizeColor($labelColor, '') : '';
        $logo   = self::sanitizeChoice($logo, self::LOGO_CHOICES, '');
        $cacheSeconds = max(0, min(86400, (int) $cacheSeconds));

        $parts = [];
        $parts[] = 'url=' . rawurlencode($source);
        $parts[] = 'query=' . rawurlencode($query);
        $parts[] = 'label=' . rawurlencode($label);
        $parts[] = 'color=' . $color;
        if ($labelColor !== '') {
            $parts[] = 'labelColor=' . $labelColor;
        }
        $parts[] = 'style=' . rawurlencode($style);
        if ($logo !== '') {
            $parts[] = 'logo=' . rawurlencode($logo);
        }
        $parts[] = 'cacheSeconds=' . $cacheSeconds;

        return 'https://img.shields.io/badge/dynamic/json?' . implode('&', $parts);
    }

    /**
     * Build the module's server-side badge SVG endpoint URL.
     */
    public static function buildBadgeSvgUrl($owner, $repo, $branch, $jsonPath, $provider, $query, $label, $color, $labelColor, $style, $logo, $cacheSeconds)
    {
        $provider = self::sanitizeChoice($provider, self::PROVIDERS, 'raw');
        $color    = self::sanitizeColor($color, '0A7EA4');
        $labelColor = self::sanitizeColor($labelColor, self::DEFAULT_LABEL_COLOR);
        $style    = self::sanitizeChoice($style, self::BADGE_STYLES, 'flat-square');
        $logo     = self::sanitizeChoice($logo, self::LOGO_CHOICES, '');
        $cacheSeconds = max(0, min(86400, (int) $cacheSeconds));

        return Uri::root() . 'index.php?option=com_ajax&module=curseforge_generator&method=badge&format=raw'
            . '&owner=' . rawurlencode($owner)
            . '&repo=' . rawurlencode($repo)
            . '&branch=' . rawurlencode($branch)
            . '&jsonpath=' . rawurlencode($jsonPath)
            . '&provider=' . rawurlencode($provider)
            . '&query=' . rawurlencode($query)
            . '&label=' . rawurlencode($label)
            . '&color=' . rawurlencode($color)
            . '&labelcolor=' . rawurlencode($labelColor)
            . '&style=' . rawurlencode($style)
            . '&logo=' . rawurlencode($logo)
            . '&cache=' . rawurlencode((string) $cacheSeconds);
    }

    /**
     * Build the module endpoint URL that renders the chart SVG.
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

    private static function measureText($text, $fontSize = 11, $letterSpacing = 0)
    {
        $width = 0;
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            if (isset(self::VERDANA_WIDTHS[$char])) {
                $width += self::VERDANA_WIDTHS[$char];
            } else {
                $width += 6.6;
            }
        }

        $width = $width * ($fontSize / 11);

        if ($letterSpacing > 0 && $length > 1) {
            $width += $letterSpacing * ($length - 1);
        }

        return $width;
    }

    private static function textColorFor($hex)
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $lin = function ($c) {
            if ($c <= 0.03928) {
                return $c / 12.92;
            }

            return pow(($c + 0.055) / 1.055, 2.4);
        };

        $l = 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);

        return $l > 0.179 ? '#0d1117' : '#ffffff';
    }

    private static function escapeXmlAttr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function escapeXmlText($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Render a shields-style badge as SVG.
     */
    public static function buildBadgeSvg($label, $message, $color, $labelColor, $style, $logo = '')
    {
        $color      = self::sanitizeColor($color, '0A7EA4');
        $labelColor = self::sanitizeColor($labelColor, self::DEFAULT_LABEL_COLOR);
        $style      = self::sanitizeChoice($style, self::BADGE_STYLES, 'flat-square');
        $logo       = self::sanitizeChoice($logo, self::LOGO_CHOICES, '');

        $isForBadge = ($style === 'for-the-badge');
        $labelText  = $isForBadge ? strtoupper((string) $label) : (string) $label;
        $msgText    = $isForBadge ? strtoupper((string) $message) : (string) $message;

        $fontSize     = $isForBadge ? 10 : 11;
        $letterSpacing = $isForBadge ? 1 : 0;
        $height       = $isForBadge ? 28 : 20;
        $radius       = ($style === 'flat-square') ? 0 : 4;
        $if $style === 'flat' $radius = 3;
        if ($style === 'flat') {
            $radius = 3;
        } elseif ($style === 'flat-square') {
            $radius = 0;
        } else {
            $radius = 4;
        }

        $baseline = $isForBadge ? 18 : 14;
        $logoPad  = ($logo === 'curseforge') ? 16 : 0;

        $labelW = (int) ceil(self::measureText($labelText, $fontSize, $letterSpacing)) + 10;
        $msgW   = (int) ceil(self::measureText($msgText, $fontSize, $letterSpacing)) + 10 + $logoPad;
        if ($msgW < $logoPad + 10) {
            $msgW = $logoPad + 10;
        }

        $total = $labelW + $msgW;
        if ($total < 40) {
            $total = 40;
        }

        $labelTextX = $labelW / 2;
        $msgAreaStart = $labelW;
        $msgTextX     = $msgAreaStart + $logoPad + (($msgW - $logoPad) / 2);

        $labelFill = '#' . $labelColor;
        $msgFill   = '#' . $color;
        $labelTextColor = self::textColorFor($labelColor);
        $msgTextColor   = self::textColorFor($color);

        $aria = self::escapeXmlAttr($labelText . ': ' . $msgText);
        $title = self::escapeXmlAttr($labelText . ': ' . $msgText);

        $svg = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="' . $total . '" height="' . $height . '" role="img" aria-label="' . $aria . '">'
            . '<title>' . $title . '</title>';

        if ($style === 'plastic' || $style === 'social') {
            $svg .= '<linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"/><stop offset="1" stop-opacity=".1"/></linearGradient>';
        }

        $svg .= '<clipPath id="r"><rect width="' . $total . '" height="' . $height . '" rx="' . $radius . '" fill="#fff"/></clipPath>'
            . '<g clip-path="url(#r)">'
            . '<rect width="' . $labelW . '" height="' . $height . '" fill="' . $labelFill . '"/>'
            . '<rect x="' . $labelW . '" width="' . $msgW . '" height="' . $height . '" fill="' . $msgFill . '"/>'
            . '<rect width="' . $total . '" height="' . $height . '" fill="url(#s)"/>'
            . '</g>'
            . '<g fill="' . $labelTextColor . '" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="' . $fontSize . '">'
            . '<text x="' . $labelTextX . '" y="' . $baseline . '" fill="' . $labelTextColor . '">' . self::escapeXmlText($labelText) . '</text>'
            . '</g>';

        if ($logo === 'curseforge') {
            $svg .= '<g transform="translate(' . ($msgAreaStart + 3) . ',' . (($height - 14) / 2) . ') scale(0.5833)"><path d="' . self::CURSEFORGE_LOGO_PATH . '" fill="#fff"/></g>';
        }

        $svg .= '<g fill="' . $msgTextColor . '" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="' . $fontSize . '"';
        if ($letterSpacing > 0) {
            $svg .= ' letter-spacing="' . $letterSpacing . '"';
        }
        $svg .= '>'
            . '<text x="' . $msgTextX . '" y="' . $baseline . '" fill="' . $msgTextColor . '">' . self::escapeXmlText($msgText) . '</text>'
            . '</g>'
            . '</svg>';

        return $svg;
    }

    private static function resolveQuery($data, $query)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return null;
        }
        if (strpos($query, '$') !== 0) {
            return null;
        }
        $rest = substr($query, 1);
        $rest = ltrim($rest, '.');
        if ($rest === '') {
            return $data;
        }

        preg_match_all("/\[\s*'([^']*)'\s*\]|\[\s*\"([^\"]*)\"\s*\]|([A-Za-z0-9_\-]+)/", $rest, $matches, PREG_SET_ORDER);

        $tokens = [];
        foreach ($matches as $match) {
            if (isset($match[3]) && $match[3] !== '') {
                $tokens[] = $match[3];
            } elseif (isset($match[1]) && $match[1] !== '') {
                $tokens[] = $match[1];
            } elseif (isset($match[2]) && $match[2] !== '') {
                $tokens[] = $match[2];
            }
        }

        if (empty($tokens)) {
            return null;
        }

        $current = $data;
        foreach ($tokens as $token) {
            if (is_array($current) && array_key_exists($token, $current)) {
                $current = $current[$token];
                continue;
            }
            return null;
        }

        return $current;
    }

    private static function fetchStats($provider, $owner, $repo, $branch, $path)
    {
        $provider = self::sanitizeChoice($provider, self::PROVIDERS, 'raw');
        $owner    = self::sanitizeSlug($owner, '');
        $repo     = self::sanitizeSlug($repo, '');
        $branch   = self::sanitizeSlug($branch, '');
        $path     = self::sanitizePath($path, '');

        if ($owner === '' || $repo === '' || $branch === '' || $path === '') {
            return null;
        }

        $key = $provider . '|' . $owner . '|' . $repo . '|' . $branch . '|' . $path;
        if (isset(self::$fetchCache[$key])) {
            return self::$fetchCache[$key];
        }

        $url = self::getSourceUrl($provider, $owner, $repo, $branch, $path);

        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => 10,
                'ignore_errors' => true,
                'header'        => "Accept: application/json\r\nUser-Agent: mod_curseforge_generator/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false || $body === '') {
            self::$fetchCache[$key] = null;
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            self::$fetchCache[$key] = null;
            return null;
        }

        self::$fetchCache[$key] = $data;
        return $data;
    }

    /**
     * Render a server-side badge SVG for com_ajax method=badge.
     */
    public static function badgeAjax()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $owner    = self::sanitizeSlug($input->getString('owner', ''), '');
        $repo     = self::sanitizeSlug($input->getString('repo', ''), '');
        $branch   = self::sanitizeSlug($input->getString('branch', ''), 'stats-data');
        $jsonPath = self::sanitizePath($input->getString('jsonpath', ''), 'curseforge/downloads.json');
        $provider = self::sanitizeChoice($input->getString('provider', 'raw'), self::PROVIDERS, 'raw');
        $query    = $input->getString('query', '$.stats.total');
        $label    = trim((string) $input->getString('label', 'downloads/total'));
        $color    = self::sanitizeColor($input->getString('color', ''), '0A7EA4');
        $labelColor = self::sanitizeColor($input->getString('labelcolor', ''), self::DEFAULT_LABEL_COLOR);
        $style    = self::sanitizeChoice($input->getString('style', 'flat-square'), self::BADGE_STYLES, 'flat-square');
        $logo     = self::sanitizeChoice($input->getString('logo', ''), self::LOGO_CHOICES, '');
        $cache    = (int) $input->getInt('cache', 900);
        $cache    = max(0, min(86400, $cache));

        $app->setHeader('Content-Type', 'image/svg+xml; charset=utf-8', true);
        if ($cache > 0) {
            $app->setHeader('Cache-Control', 'public, max-age=' . $cache, true);
        } else {
            $app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
        }

        if ($label === '') {
            $label = 'downloads';
        }

        if ($owner === '' || $repo === '') {
            return self::buildBadgeSvg($label, 'unavailable', '9f9f9f', $labelColor, $style, $logo);
        }

        $data = self::fetchStats($provider, $owner, $repo, $branch, $jsonPath);
        $value = $data === null ? null : self::resolveQuery($data, $query);

        if ($value === null || $value === '') {
            return self::buildBadgeSvg($label, 'unavailable', $color, $labelColor, $style, $logo);
        }

        if (is_numeric($value)) {
            $message = number_format((float) $value, 0, '.', ',');
        } else {
            $message = (string) $value;
        }

        if ($message === '') {
            $message = 'unavailable';
            return self::buildBadgeSvg($label, $message, $color, $labelColor, $style, $logo);
        }

        return self::buildBadgeSvg($label, $message, $color, $labelColor, $style, $logo);
    }

    /**
     * Parse date and count lines (YYYY-MM-DD count) into a sorted map.
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
                $firstKey = array_key_first($chunk);
                $groups[$firstKey] = array_sum($chunk);
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
     */
    public static function buildChartSvg(array $labels, array $values, $theme, $color, $title)
    {
        $palettes = [
            'black'  => ['background' => '#0b0b0d', 'grid' => '#1f2937', 'axis' => '#94a3b8', 'text' => '#f8fafc', 'sub' => '#cbd5e1', 'line' => '#22d3ee'],
            'slate'  => ['background' => '#0f172a', 'grid' => '#1e293b', 'axis' => '#94a3b8', 'text' => '#e2e8f0', 'sub' => '#94a3b8', 'line' => '#56ABC8'],
            'orange' => ['background' => '#fff7ed', 'grid' => '#fed7aa', 'axis' => '#c2410c', 'text' => '#7c2d12', 'sub' => '#9a3412', 'line' => '#ea580c'],
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
        $stroke = $palette['line'] ?? ('#' . $color);
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
                . '<polyline points="' . implode(' ', $line) . '" fill="none" stroke="' . $stroke . '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>';

            $svg .= '<circle cx="' . $last[0] . '" cy="' . $last[1] . '" r="4.5" fill="' . $stroke . '"/>';

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
     */
    public static function getAjax()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $post     = $input->post;
        $owner    = $post->get('owner', '', 'STRING');
        $repo     = $post->get('repo', '', 'STRING');
        $branch   = $post->get('branch', '', 'STRING');
        $jsonPath = $post->get('jsonpath', 'curseforge/downloads.json', 'STRING');

        // Badge fields
        $metric     = $post->get('metric', 'total|$.stats.total|0A7EA4', 'STRING');
        $label      = $post->get('label', 'downloads/total', 'STRING');
        $color      = $post->get('color', '0A7EA4', 'STRING');
        $style      = $post->get('style', 'flat-square', 'STRING');
        $provider   = $post->get('provider', 'raw', 'STRING');
        $labelColor = $post->get('labelcolor', '', 'STRING');
        $logo       = $post->get('logo', '', 'STRING');
        $cache      = $post->get('cache', '900', 'STRING');

        // Chart fields
        $chartType = self::sanitizeChoice($post->get('charttype', 'total-trend', 'STRING'), ['total-trend', 'daily', 'weekly', 'monthly', 'yearly'], 'total-trend');
        $theme     = self::sanitizeChoice($post->get('theme', 'slate', 'STRING'), ['black', 'slate', 'orange'], 'slate');
        $chartData = $post->get('chartdata', '', 'RAW');

        // Sanitize
        $owner    = self::sanitizeSlug($owner, 'OWNER');
        $repo     = self::sanitizeSlug($repo, 'REPOSITORY');
        $branch   = self::sanitizeSlug($branch, 'stats-data');
        $jsonPath = self::sanitizePath($jsonPath, 'curseforge/downloads.json');
        $provider = self::sanitizeChoice($provider, self::PROVIDERS, 'raw');
        $style    = self::sanitizeChoice($style, self::BADGE_STYLES, 'flat-square');
        $labelColor = $labelColor !== '' ? self::sanitizeColor($labelColor, self::DEFAULT_LABEL_COLOR) : self::DEFAULT_LABEL_COLOR;
        $logo     = self::sanitizeChoice($logo, self::LOGO_CHOICES, '');
        $cache    = max(0, min(86400, (int) $cache));

        // Parse metric
        $metricParts  = explode('|', $metric);
        $defaultQuery = $metricParts[1] ?? '$.stats.total';
        $defaultColor = $metricParts[2] ?? '0A7EA4';
        $query        = $defaultQuery;
        $color        = self::sanitizeColor($color, $defaultColor);

        if ($label === '' || trim((string) $label) === '') {
            $label = 'downloads/' . ($metricParts[0] ?? 'total');
        } else {
            $label = trim((string) $label);
        }

        // Build badge URLs (server-side primary, shields.io secondary)
        $badgeSvgUrl = self::buildBadgeSvgUrl($owner, $repo, $branch, $jsonPath, $provider, $query, $label, $color, $labelColor, $style, $logo, $cache);
        $badgeUrl    = $badgeSvgUrl;
        $badgeMd     = '![' . $label . '](' . $badgeSvgUrl . ')';

        $shieldsUrl  = self::buildBadgeUrl($owner, $repo, $branch, $jsonPath, $query, $label, $color, $style, $labelColor, $logo, $cache, $provider);
        $shieldsMd   = '![' . $label . '](' . $shieldsUrl . ')';

        // Build chart
        $chartUrl = self::buildChartUrl($owner, $repo, $chartType, $theme, $chartData, $color);
        $chartMd  = '![Downloads ' . $chartType . ' ' . $theme . '](' . $chartUrl . ')';

        $response = [
            'success' => true,
            'badge'   => [
                'url'        => $badgeUrl,
                'md'         => $badgeMd,
                'label'      => $label,
                'shieldsUrl' => $shieldsUrl,
                'shieldsMd'  => $shieldsMd,
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
     */
    public static function chartAjax()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

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
