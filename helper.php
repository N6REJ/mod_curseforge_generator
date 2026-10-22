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

/**
 * Helper for mod_curseforge_generator.
 *
 * Renders badges and charts server-side, following the conventions of the
 * justagwas GitHub Downloads Action (GDA) generator lab:
 * https://www.justagwas.com/projects/gda/
 */
class ModCurseforgeGeneratorHelper
{
    private const SAFE_SLUG  = '/^[A-Za-z0-9_.-]+$/';
    private const SAFE_PATH  = '/^[A-Za-z0-9_.\-\/]+$/';
    private const SAFE_COLOR = '/^[0-9A-Fa-f]{6}$/';

    public const PROVIDERS    = ['raw', 'jsdelivr'];
    public const BADGE_STYLES = ['flat', 'flat-square', 'plastic', 'for-the-badge', 'social'];
    public const LOGO_CHOICES = ['', 'curseforge', 'github'];

    /**
     * Metric definitions: query path, default badge colour and default label.
     * Colours follow the justagwas GDA reference dashboard:
     * total 0A7EA4, day 1E8E3E, week 0069C2, month B26A00.
     */
    public const METRICS = [
        'total'   => ['query' => '$.stats.total',         'color' => '0A7EA4', 'label' => 'downloads/total'],
        'day'     => ['query' => '$.stats.day',           'color' => '1E8E3E', 'label' => 'downloads/day'],
        'week'    => ['query' => '$.stats.week',          'color' => '0069C2', 'label' => 'downloads/week'],
        'month'   => ['query' => '$.stats.month',         'color' => 'B26A00', 'label' => 'downloads/month'],
        'year'    => ['query' => '$.stats.year',          'color' => '6A1B9A', 'label' => 'downloads/year'],
        'profile' => ['query' => '$.profile.defaultMode', 'color' => '5E35B1', 'label' => 'downloads/profile'],
    ];

    public const DEFAULT_LABEL_COLOR   = '555555';
    public const DEFAULT_CACHE_SECONDS = 900;

    public const CHART_TYPES              = ['total-trend', 'daily', 'weekly', 'monthly', 'yearly'];
    public const CHART_THEMES             = ['black', 'slate', 'orange'];
    public const CHART_DATE_LABEL_FORMATS = ['yyyy-mm-dd', 'yy/mm/dd', 'dd/mm', 'mm/dd', 'none'];
    public const CHART_TITLE_MODES        = ['default', 'custom', 'none'];

    /**
     * Per chart type metadata, mirroring the GDA action chart config.
     */
    public const CHART_TYPE_META = [
        'total-trend' => ['latestLabel' => 'Latest total', 'bucket' => 'total', 'rangeDays' => 0],
        'daily'       => ['latestLabel' => 'Latest day',   'bucket' => 'day',   'rangeDays' => 1],
        'weekly'      => ['latestLabel' => 'Latest week',  'bucket' => 'week',  'rangeDays' => 7],
        'monthly'     => ['latestLabel' => 'Latest month', 'bucket' => 'month', 'rangeDays' => 30],
        'yearly'      => ['latestLabel' => 'Latest year',  'bucket' => 'year',  'rangeDays' => 365],
    ];

    /**
     * Per theme palette, mirroring the GDA action chart config.
     */
    public const CHART_THEME_META = [
        'black'  => [
            'background' => '#0b0b0d', 'title' => '#f8fafc', 'subtitle' => '#cbd5e1',
            'grid' => '#1f2937', 'axis' => '#94a3b8', 'line' => '#22d3ee', 'dot' => '#22d3ee',
            'latest' => '#f8fafc', 'fill' => '#22d3ee', 'empty' => '#cbd5e1', 'value' => '#cbd5e1',
        ],
        'slate'  => [
            'background' => '#0f172a', 'title' => '#e2e8f0', 'subtitle' => '#94a3b8',
            'grid' => '#1e293b', 'axis' => '#94a3b8', 'line' => '#60a5fa', 'dot' => '#60a5fa',
            'latest' => '#e2e8f0', 'fill' => '#60a5fa', 'empty' => '#cbd5e1', 'value' => '#cbd5e1',
        ],
        'orange' => [
            'background' => '#fff7ed', 'title' => '#7c2d12', 'subtitle' => '#9a3412',
            'grid' => '#fed7aa', 'axis' => '#c2410c', 'line' => '#ea580c', 'dot' => '#ea580c',
            'latest' => '#7c2d12', 'fill' => '#f97316', 'empty' => '#9a3412', 'value' => '#9a3412',
        ],
    ];

    private const CURSEFORGE_LOGO_PATH = 'M18.326 9.2145S23.2261 8.4418 24 6.1882h-7.5066V4.4H0l2.0318 2.3576V9.173s5.1267-.2665 7.1098 1.2372c2.7146 2.516-3.053 5.917-3.053 5.917L5.0995 19.6c1.5465-1.4726 4.494-3.3775 9.8983-3.2857-2.0565.65-4.1245 1.6651-5.7344 3.2857h10.9248l-1.0288-3.2726s-7.918-4.6688-.8336-7.1127z';

    private const GITHUB_LOGO_PATH = 'M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12';

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
     * Clamp an integer into a range.
     */
    public static function clampInt($value, $min, $max, $fallback)
    {
        $value = (int) $value;

        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
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
     *
     * @param   string  $owner    GitHub owner
     * @param   string  $repo     Repository name
     * @param   array   $options  Chart options
     *
     * @return  string
     */
    public static function buildChartUrl($owner, $repo, array $options)
    {
        $params = [
            'owner'            => $owner,
            'repo'             => $repo,
            'branch'           => $options['branch'],
            'jsonpath'         => $options['jsonpath'],
            'provider'         => $options['provider'],
            'charttype'        => $options['charttype'],
            'theme'            => $options['theme'],
            'width'            => $options['width'],
            'height'           => $options['height'],
            'zerobaseline'     => $options['zerobaseline'] ? 1 : 0,
            'yticks'           => $options['yticks'],
            'xlabeleverydays'  => $options['xlabeleverydays'],
            'showvaluelabels'  => $options['showvaluelabels'] ? 1 : 0,
            'datelabelformat'  => $options['datelabelformat'],
            'showgeneratedat'  => $options['showgeneratedat'] ? 1 : 0,
            'titlemode'        => $options['titlemode'],
            'titletext'        => $options['titletext'],
            'chartalt'         => $options['chartalt'],
        ];

        if ($options['chartdata'] !== '') {
            $params['chartdata'] = $options['chartdata'];
        }

        $query = [];

        foreach ($params as $key => $value) {
            $query[] = $key . '=' . rawurlencode((string) $value);
        }

        return Uri::root() . 'index.php?option=com_ajax&module=curseforge_generator&method=chart&format=raw&' . implode('&', $query);
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

        $fontSize      = $isForBadge ? 10 : 11;
        $letterSpacing = $isForBadge ? 1 : 0;
        $height        = $isForBadge ? 28 : 20;

        if ($style === 'flat') {
            $radius = 3;
        } elseif ($style === 'flat-square') {
            $radius = 0;
        } else {
            $radius = 4;
        }

        $baseline = $isForBadge ? 18 : 14;
        $logoPad  = ($logo !== '') ? 16 : 0;

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
            $svg .= '<linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#fff" stop-opacity=".7"/><stop offset=".1" stop-color="#fff" stop-opacity=".1"/><stop offset=".9" stop-color="#000" stop-opacity=".1"/><stop offset="1" stop-color="#000" stop-opacity=".2"/></linearGradient>';
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

        if ($logo !== '') {
            $logoPath = ($logo === 'github') ? self::GITHUB_LOGO_PATH : self::CURSEFORGE_LOGO_PATH;
            $svg .= '<g transform="translate(' . ($msgAreaStart + 3) . ',' . (($height - 14) / 2) . ') scale(0.5833)"><path d="' . $logoPath . '" fill="#fff"/></g>';
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
     * Extract the daily snapshot series (date => total) from a stats payload.
     */
    public static function extractSeries($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $series = [];

        if (isset($data['snapshots']['series']) && is_array($data['snapshots']['series'])) {
            $series = $data['snapshots']['series'];
        } elseif (isset($data['series']) && is_array($data['series'])) {
            $series = $data['series'];
        }

        return self::normalizeSeries($series);
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
        $jsonPath = self::sanitizePath($input->getString('jsonpath', ''), 'stats/downloads.json');
        $provider = self::sanitizeChoice($input->getString('provider', 'raw'), self::PROVIDERS, 'raw');
        $query    = $input->getString('query', '$.stats.total');
        $label    = trim((string) $input->getString('label', 'downloads/total'));
        $color    = self::sanitizeColor($input->getString('color', ''), '0A7EA4');
        $labelColor = self::sanitizeColor($input->getString('labelcolor', ''), self::DEFAULT_LABEL_COLOR);
        $style    = self::sanitizeChoice($input->getString('style', 'flat-square'), self::BADGE_STYLES, 'flat-square');
        $logo     = self::sanitizeChoice($input->getString('logo', ''), self::LOGO_CHOICES, '');
        $cache    = (int) $input->getInt('cache', self::DEFAULT_CACHE_SECONDS);
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
            return self::buildBadgeSvg($label, 'unavailable', $color, $labelColor, $style, $logo);
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
        }

        return self::buildBadgeSvg($label, $message, $color, $labelColor, $style, $logo);
    }

    /* ---------------------------------------------------------------------
     * Chart helpers (ported from the GDA action chart.js / chart-config.js)
     * ------------------------------------------------------------------ */

    /**
     * Keep only valid YYYY-MM-DD entries with non negative values.
     */
    public static function normalizeSeries($series)
    {
        if (!is_array($series)) {
            return [];
        }

        $normalized = [];

        foreach ($series as $date => $value) {
            if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            if (!is_numeric($value)) {
                continue;
            }

            $value = (float) $value;

            if (!is_finite($value) || $value < 0) {
                continue;
            }

            $normalized[$date] = (int) floor($value);
        }

        ksort($normalized);

        return $normalized;
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

    private static function shiftDate($dateString, $daysAgo)
    {
        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));
        $days = (int) $daysAgo;

        if ($days !== 0) {
            $date = $date->modify('-' . $days . ' days');
        }

        return $date->format('Y-m-d');
    }

    private static function startOfIsoWeek($dateString)
    {
        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));
        $isoDay = (int) $date->format('N');

        return $date->modify('-' . ($isoDay - 1) . ' days')->format('Y-m-d');
    }

    private static function startOfMonth($dateString)
    {
        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));

        return $date->setDate((int) $date->format('Y'), (int) $date->format('m'), 1)->format('Y-m-d');
    }

    private static function startOfYear($dateString)
    {
        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));

        return $date->setDate((int) $date->format('Y'), 1, 1)->format('Y-m-d');
    }

    /**
     * Find the entry used as the baseline for a bucket.
     */
    private static function pickBaseline(array $entries, $cutoffDate, $maxIndex)
    {
        for ($index = 0; $index <= $maxIndex; $index++) {
            if ($entries[$index][0] === $cutoffDate) {
                return $entries[$index];
            }
        }

        for ($index = 0; $index <= $maxIndex; $index++) {
            if ($entries[$index][0] > $cutoffDate) {
                return $entries[$index];
            }
        }

        for ($index = $maxIndex; $index >= 0; $index--) {
            if ($entries[$index][0] < $cutoffDate) {
                return $entries[$index];
            }
        }

        return null;
    }

    /**
     * Convert the total trend into the requested bucket values.
     */
    private static function toChartEntries(array $entries, $chartType)
    {
        if ($chartType === 'total-trend') {
            $result = [];

            foreach ($entries as $entry) {
                $result[] = ['date' => $entry[0], 'value' => $entry[1]];
            }

            return $result;
        }

        $meta = self::CHART_TYPE_META[$chartType] ?? self::CHART_TYPE_META['daily'];
        $bucket = $meta['bucket'];

        $result = [];

        foreach ($entries as $index => $entry) {
            if ($bucket === 'day') {
                $bucketStart = self::shiftDate($entry[0], 1);
            } elseif ($bucket === 'week') {
                $bucketStart = self::startOfIsoWeek($entry[0]);
            } elseif ($bucket === 'month') {
                $bucketStart = self::startOfMonth($entry[0]);
            } else {
                $bucketStart = self::startOfYear($entry[0]);
            }

            $baseline = self::pickBaseline($entries, $bucketStart, $index);
            $baselineValue = $baseline !== null ? $baseline[1] : $entry[1];

            $result[] = ['date' => $entry[0], 'value' => max(0, $entry[1] - $baselineValue)];
        }

        return $result;
    }

    /**
     * Proportional chart layout.
     */
    private static function buildChartLayout($width, $height)
    {
        $left   = max(56, (int) round($width * 0.064));
        $right  = max(24, (int) round($width * 0.024));
        $top    = max(48, (int) round($height * 0.144));
        $bottom = max(52, (int) round($height * 0.156));

        return [
            'left'         => $left,
            'right'        => $right,
            'top'          => $top,
            'bottom'       => $bottom,
            'titleSize'    => max(18, (int) round($height * 0.067)),
            'subtitleSize' => max(11, (int) round($height * 0.036)),
            'axisSize'     => max(10, (int) round($height * 0.033)),
            'latestSize'   => max(12, (int) round($height * 0.039)),
            'emptySize'    => max(14, (int) round($height * 0.044)),
            'valueSize'    => max(9, (int) round($height * 0.03)),
        ];
    }

    /**
     * Pick the point indices that receive an x axis label.
     */
    private static function buildXLabelIndices(array $points, $xLabelEveryDays)
    {
        $count = count($points);

        if ($count <= 1) {
            return [0];
        }

        $indices = [0];

        if ($xLabelEveryDays > 0) {
            $lastLabelDate = $points[0]['date'];

            for ($index = 1; $index < $count - 1; $index++) {
                if (self::daysBetween($points[$index]['date'], $lastLabelDate) >= $xLabelEveryDays) {
                    $indices[] = $index;
                    $lastLabelDate = $points[$index]['date'];
                }
            }
        } else {
            $maxLabels = 6;
            $step = max(1, (int) floor(($count - 1) / ($maxLabels - 1)));

            for ($index = $step; $index < $count - 1; $index += $step) {
                $indices[] = $index;
            }
        }

        $lastIndex = $count - 1;

        if (!in_array($lastIndex, $indices, true)) {
            $indices[] = $lastIndex;
        }

        return $indices;
    }

    private static function toDayNumber($dateString)
    {
        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));

        return (int) floor($date->getTimestamp() / 86400);
    }

    private static function daysBetween($dateA, $dateB)
    {
        return abs(self::toDayNumber($dateA) - self::toDayNumber($dateB));
    }

    private static function formatNumber($value)
    {
        return number_format((float) $value, 0, '.', ',');
    }

    private static function formatDateLabel($dateString, $dateLabelFormat)
    {
        if ($dateLabelFormat === 'none') {
            return '';
        }

        if ($dateLabelFormat === 'yyyy-mm-dd') {
            return $dateString;
        }

        $date = new \DateTimeImmutable($dateString . ' 00:00:00', new \DateTimeZone('UTC'));
        $year = (int) $date->format('Y');
        $yy = str_pad((string) ($year % 100), 2, '0', STR_PAD_LEFT);
        $mm = $date->format('m');
        $dd = $date->format('d');

        if ($dateLabelFormat === 'yy/mm/dd') {
            return $yy . '/' . $mm . '/' . $dd;
        }

        if ($dateLabelFormat === 'dd/mm') {
            return $dd . '/' . $mm;
        }

        if ($dateLabelFormat === 'mm/dd') {
            return $mm . '/' . $dd;
        }

        return $dateString;
    }

    /**
     * Format the generated timestamp using the requested date label format.
     */
    public static function formatGeneratedDate($generatedAt, $dateLabelFormat)
    {
        $generatedAt = (string) $generatedAt;

        if (strlen($generatedAt) < 10) {
            return $generatedAt;
        }

        $datePart = substr($generatedAt, 0, 10);
        $format = $dateLabelFormat === 'none' ? 'yyyy-mm-dd' : $dateLabelFormat;

        return self::formatDateLabel($datePart, $format);
    }

    private static function buildChartTitle($owner, $repo, $titleMode, $titleText)
    {
        if ($titleMode === 'none') {
            return '';
        }

        if ($titleMode === 'custom') {
            $trimmed = trim((string) $titleText);

            return $trimmed !== '' ? $trimmed : $owner . '/' . $repo . ' downloads';
        }

        return $owner . '/' . $repo . ' downloads';
    }

    /**
     * Build the chart SVG.
     *
     * @param   array  $series   date => value map
     * @param   array  $options  chart options
     *
     * @return  string
     */
    public static function buildChartSvg(array $series, array $options = [])
    {
        $options = array_merge([
            'owner'            => 'owner',
            'repo'             => 'repo',
            'generatedAt'      => '',
            'chartType'        => 'total-trend',
            'chartTheme'       => 'slate',
            'width'            => 1000,
            'height'           => 360,
            'zeroBaseline'     => true,
            'yTicks'           => 6,
            'xLabelEveryDays'  => 0,
            'showValueLabels'  => false,
            'dateLabelFormat'  => 'yyyy-mm-dd',
            'showGeneratedAt'  => true,
            'titleMode'        => 'default',
            'titleText'        => '',
            'altText'          => '',
        ], $options);

        $owner = (string) $options['owner'];
        $repo  = (string) $options['repo'];

        $chartType = self::sanitizeChoice($options['chartType'], self::CHART_TYPES, 'total-trend');
        $chartTheme = self::sanitizeChoice($options['chartTheme'], self::CHART_THEMES, 'slate');
        $dateLabelFormat = self::sanitizeChoice($options['dateLabelFormat'], self::CHART_DATE_LABEL_FORMATS, 'yyyy-mm-dd');
        $titleMode = self::sanitizeChoice($options['titleMode'], self::CHART_TITLE_MODES, 'default');

        $width  = self::clampInt($options['width'], 640, 4096, 1000);
        $height = self::clampInt($options['height'], 240, 2160, 360);
        $yTicks = self::clampInt($options['yTicks'], 2, 12, 6);
        $xLabelEveryDays = self::clampInt($options['xLabelEveryDays'], 0, 365, 0);

        $typeMeta  = self::CHART_TYPE_META[$chartType];
        $themeMeta = self::CHART_THEME_META[$chartTheme];

        $layout = self::buildChartLayout($width, $height);
        $left   = $layout['left'];
        $right  = $layout['right'];
        $top    = $layout['top'];
        $bottom = $layout['bottom'];

        $plotWidth  = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;

        $title    = self::buildChartTitle($owner, $repo, $titleMode, $options['titleText']);
        $subtitle = $options['showGeneratedAt']
            ? 'Generated ' . self::formatGeneratedDate($options['generatedAt'], $dateLabelFormat)
            : '';

        $ariaLabel = trim((string) $options['altText']);

        if ($ariaLabel === '') {
            $ariaLabel = ($title !== '' ? $title : $owner . '/' . $repo . ' downloads') . ' chart';
        }

        $gradientId = 'fill-' . preg_replace('/[^a-z0-9]/', '-', strtolower($chartType)) . '-' . $chartTheme;

        $titleNode = $title !== ''
            ? '<text class="title" x="' . $left . '" y="' . (int) round($top * 0.65) . '">' . self::escapeXmlText($title) . '</text>'
            : '';

        $subtitleNode = $subtitle !== ''
            ? '<text class="sub" x="' . $left . '" y="' . ($height - max(8, (int) round($layout['subtitleSize'] * 0.46))) . '">' . self::escapeXmlText($subtitle) . '</text>'
            : '';

        $entries = self::normalizeSeries($series);

        if (empty($entries)) {
            return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="'
                . self::escapeXmlAttr($ariaLabel) . '">' . "\n"
                . '  <defs>' . "\n"
                . '    <style>' . "\n"
                . '      .bg { fill: ' . $themeMeta['background'] . '; }' . "\n"
                . '      .title { font: 700 ' . $layout['titleSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['title'] . '; }' . "\n"
                . '      .sub { font: 400 ' . $layout['subtitleSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['subtitle'] . '; }' . "\n"
                . '      .empty { font: 600 ' . $layout['emptySize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['empty'] . '; }' . "\n"
                . '    </style>' . "\n"
                . '  </defs>' . "\n"
                . '  <rect class="bg" x="0" y="0" width="' . $width . '" height="' . $height . '" rx="16" />' . "\n"
                . '  ' . $titleNode . "\n"
                . '  ' . $subtitleNode . "\n"
                . '  <text class="empty" x="' . (int) round($width / 2) . '" y="' . (int) round($height / 2) . '" text-anchor="middle">No snapshot data yet</text>' . "\n"
                . '</svg>' . "\n";
        }

        $chartEntries = self::toChartEntries(array_values(array_map(null, array_keys($entries), array_values($entries))), $chartType);

        $values  = array_column($chartEntries, 'value');
        $rawMin  = min($values);
        $rawMax  = max($values);
        $scaleMin = $options['zeroBaseline'] ? 0 : $rawMin;
        $scaleMax = $rawMax;

        if ($scaleMax <= $scaleMin) {
            $scaleMax = $scaleMin + 1;
        }

        $safeRange   = $scaleMax - $scaleMin;
        $latestValue = $chartEntries[count($chartEntries) - 1]['value'];
        $count       = count($chartEntries);

        $points = [];

        foreach ($chartEntries as $index => $entry) {
            $x = $count > 1 ? $left + ($index / ($count - 1)) * $plotWidth : $left + $plotWidth / 2;
            $y = $top + (($scaleMax - $entry['value']) / $safeRange) * $plotHeight;

            $points[] = [
                'date'  => $entry['date'],
                'value' => $entry['value'],
                'x'     => $x,
                'y'     => $y,
            ];
        }

        $pointText = [];

        foreach ($points as $point) {
            $pointText[] = number_format($point['x'], 2, '.', '') . ',' . number_format($point['y'], 2, '.', '');
        }

        $areaPath = 'M ' . number_format($points[0]['x'], 2, '.', '') . ' ' . ($top + $plotHeight);

        foreach ($points as $point) {
            $areaPath .= ' L ' . number_format($point['x'], 2, '.', '') . ' ' . number_format($point['y'], 2, '.', '');
        }

        $areaPath .= ' L ' . number_format($points[$count - 1]['x'], 2, '.', '') . ' ' . ($top + $plotHeight) . ' Z';

        $grid = [];

        for ($index = 0; $index <= $yTicks; $index++) {
            $y = $top + ($index / $yTicks) * $plotHeight;
            $value = (int) round($scaleMax - ($index / $yTicks) * $safeRange);

            $grid[] = '<line x1="' . $left . '" y1="' . number_format($y, 2, '.', '') . '" x2="' . ($width - $right) . '" y2="' . number_format($y, 2, '.', '') . '" class="grid"/>';
            $grid[] = '<text x="' . ($left - 8) . '" y="' . number_format($y + 4, 2, '.', '') . '" text-anchor="end" class="axis">' . self::escapeXmlText(self::formatNumber($value)) . '</text>';
        }

        $xLabelIndices = self::buildXLabelIndices($points, $xLabelEveryDays);
        $xLabelY = $height - max(22, (int) round($bottom * 0.43));
        $xLabels = [];

        if ($dateLabelFormat !== 'none') {
            foreach ($xLabelIndices as $index) {
                $point = $points[$index];
                $isFirst = $index === 0;
                $isLast = $index === $count - 1;
                $anchor = $isFirst ? 'start' : ($isLast ? 'end' : 'middle');

                $xLabels[] = '<text class="axis" x="' . number_format($point['x'], 2, '.', '') . '" y="' . $xLabelY . '" text-anchor="' . $anchor . '">'
                    . self::escapeXmlText(self::formatDateLabel($point['date'], $dateLabelFormat)) . '</text>';
            }
        }

        $valueLabels = '';

        if ($options['showValueLabels']) {
            $parts = [];

            foreach ($points as $point) {
                $labelY = max($top + $layout['valueSize'], $point['y'] - 8);
                $parts[] = '<text class="value" x="' . number_format($point['x'], 2, '.', '') . '" y="' . number_format($labelY, 2, '.', '') . '" text-anchor="middle">'
                    . self::escapeXmlText(self::formatNumber($point['value'])) . '</text>';
            }

            $valueLabels = implode("\n  ", $parts);
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="'
            . self::escapeXmlAttr($ariaLabel) . '">' . "\n"
            . '  <defs>' . "\n"
            . '    <linearGradient id="' . $gradientId . '" x1="0" y1="0" x2="0" y2="1">' . "\n"
            . '      <stop offset="0%" stop-color="' . $themeMeta['fill'] . '" stop-opacity="0.32"/>' . "\n"
            . '      <stop offset="100%" stop-color="' . $themeMeta['fill'] . '" stop-opacity="0.05"/>' . "\n"
            . '    </linearGradient>' . "\n"
            . '    <style>' . "\n"
            . '      .bg { fill: ' . $themeMeta['background'] . '; }' . "\n"
            . '      .title { font: 700 ' . $layout['titleSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['title'] . '; }' . "\n"
            . '      .sub { font: 400 ' . $layout['subtitleSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['subtitle'] . '; }' . "\n"
            . '      .grid { stroke: ' . $themeMeta['grid'] . '; stroke-width: 1; }' . "\n"
            . '      .axis { font: 400 ' . $layout['axisSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['axis'] . '; }' . "\n"
            . '      .line { fill: none; stroke: ' . $themeMeta['line'] . '; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }' . "\n"
            . '      .dot { fill: ' . $themeMeta['dot'] . '; }' . "\n"
            . '      .value { font: 600 ' . $layout['valueSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['value'] . '; }' . "\n"
            . '      .latest { font: 700 ' . $layout['latestSize'] . 'px Arial, sans-serif; fill: ' . $themeMeta['latest'] . '; }' . "\n"
            . '    </style>' . "\n"
            . '  </defs>' . "\n"
            . '  <rect class="bg" x="0" y="0" width="' . $width . '" height="' . $height . '" rx="16" />' . "\n"
            . '  ' . $titleNode . "\n"
            . '  ' . $subtitleNode . "\n"
            . '  ' . implode("\n  ", $grid) . "\n"
            . '  <path d="' . $areaPath . '" fill="url(#' . $gradientId . ')"/>' . "\n"
            . '  <polyline class="line" points="' . implode(' ', $pointText) . '"/>' . "\n"
            . '  <circle class="dot" cx="' . number_format($points[$count - 1]['x'], 2, '.', '') . '" cy="' . number_format($points[$count - 1]['y'], 2, '.', '') . '" r="4.5"/>' . "\n"
            . '  ' . $valueLabels . "\n"
            . '  ' . implode("\n  ", $xLabels) . "\n"
            . '  <text class="latest" x="' . ($width - $right) . '" y="' . (int) round($top * 0.65) . '" text-anchor="end">'
            . self::escapeXmlText($typeMeta['latestLabel'] . ': ' . self::formatNumber($latestValue)) . '</text>' . "\n"
            . '</svg>' . "\n";
    }

    /**
     * Collect and sanitize the chart options from a request.
     *
     * @param   object  $input  Joomla input object (request)
     *
     * @return  array
     */
    public static function chartOptionsFromRequest($input)
    {
        $string = function ($key, $fallback) use ($input) {
            return trim((string) $input->getString($key, $fallback));
        };

        $int = function ($key, $fallback) use ($input) {
            return (int) $input->getInt($key, $fallback);
        };

        return [
            'owner'           => $string('owner', 'owner'),
            'repo'            => $string('repo', 'repo'),
            'branch'          => self::sanitizeSlug($string('branch', 'stats-data'), 'stats-data'),
            'jsonpath'        => self::sanitizePath($string('jsonpath', 'stats/downloads.json'), 'stats/downloads.json'),
            'provider'        => self::sanitizeChoice($string('provider', 'raw'), self::PROVIDERS, 'raw'),
            'charttype'       => self::sanitizeChoice($string('charttype', 'total-trend'), self::CHART_TYPES, 'total-trend'),
            'theme'           => self::sanitizeChoice($string('theme', 'slate'), self::CHART_THEMES, 'slate'),
            'width'           => self::clampInt($int('width', 1000), 640, 4096, 1000),
            'height'          => self::clampInt($int('height', 360), 240, 2160, 360),
            'zerobaseline'    => $int('zerobaseline', 1) > 0,
            'yticks'          => self::clampInt($int('yticks', 6), 2, 12, 6),
            'xlabeleverydays' => self::clampInt($int('xlabeleverydays', 0), 0, 365, 0),
            'showvaluelabels' => $int('showvaluelabels', 0) > 0,
            'datelabelformat' => self::sanitizeChoice($string('datelabelformat', 'yyyy-mm-dd'), self::CHART_DATE_LABEL_FORMATS, 'yyyy-mm-dd'),
            'showgeneratedat' => $int('showgeneratedat', 1) > 0,
            'titlemode'       => self::sanitizeChoice($string('titlemode', 'default'), self::CHART_TITLE_MODES, 'default'),
            'titletext'       => mb_substr($string('titletext', ''), 0, 120),
            'chartalt'        => mb_substr($string('chartalt', ''), 0, 200),
            'chartdata'       => (string) $input->getString('chartdata', ''),
        ];
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
        $jsonPath = $post->get('jsonpath', 'stats/downloads.json', 'STRING');

        // Badge fields
        $metric     = $post->get('metric', 'total|$.stats.total|0A7EA4|downloads/total', 'STRING');
        $label      = $post->get('label', 'downloads/total', 'STRING');
        $color      = $post->get('color', '0A7EA4', 'STRING');
        $style      = $post->get('style', 'flat-square', 'STRING');
        $provider   = $post->get('provider', 'raw', 'STRING');
        $labelColor = $post->get('labelcolor', '', 'STRING');
        $logo       = $post->get('logo', '', 'STRING');
        $cache      = $post->get('cache', (string) self::DEFAULT_CACHE_SECONDS, 'STRING');

        // Chart fields
        $chartType   = $post->get('charttype', 'total-trend', 'STRING');
        $theme       = $post->get('theme', 'slate', 'STRING');
        $chartData   = $post->get('chartdata', '', 'RAW');
        $width       = $post->get('width', '1000', 'STRING');
        $height      = $post->get('height', '360', 'STRING');
        $yTicks      = $post->get('yticks', '6', 'STRING');
        $xLabelDays  = $post->get('xlabeleverydays', '0', 'STRING');
        $dateFormat  = $post->get('datelabelformat', 'yyyy-mm-dd', 'STRING');
        $titleMode   = $post->get('titlemode', 'default', 'STRING');
        $titleText   = $post->get('titletext', '', 'STRING');
        $chartAlt    = $post->get('chartalt', '', 'STRING');
        $zeroBase    = $post->get('zerobaseline', '1', 'STRING');
        $showValues  = $post->get('showvaluelabels', '0', 'STRING');
        $showGen     = $post->get('showgeneratedat', '1', 'STRING');

        // Sanitize
        $owner    = self::sanitizeSlug($owner, 'OWNER');
        $repo     = self::sanitizeSlug($repo, 'REPOSITORY');
        $branch   = self::sanitizeSlug($branch, 'stats-data');
        $jsonPath = self::sanitizePath($jsonPath, 'stats/downloads.json');
        $provider = self::sanitizeChoice($provider, self::PROVIDERS, 'raw');
        $style    = self::sanitizeChoice($style, self::BADGE_STYLES, 'flat-square');
        $labelColor = $labelColor !== '' ? self::sanitizeColor($labelColor, self::DEFAULT_LABEL_COLOR) : self::DEFAULT_LABEL_COLOR;
        $logo     = self::sanitizeChoice($logo, self::LOGO_CHOICES, '');
        $cache    = max(0, min(86400, (int) $cache));

        // Parse metric (packed as key|query|color|label)
        $metricParts  = explode('|', $metric);
        $metricKey    = $metricParts[0] ?? 'total';
        $defaultQuery = $metricParts[1] ?? '$.stats.total';
        $defaultColor = $metricParts[2] ?? '0A7EA4';
        $defaultLabel = $metricParts[3] ?? (self::METRICS[$metricKey]['label'] ?? 'downloads/total');
        $query        = $defaultQuery;
        $color        = self::sanitizeColor($color, $defaultColor);

        if ($label === '' || trim((string) $label) === '') {
            $label = $defaultLabel;
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
        $chartOptions = [
            'owner'           => $owner,
            'repo'            => $repo,
            'branch'          => $branch,
            'jsonpath'        => $jsonPath,
            'provider'        => $provider,
            'charttype'       => self::sanitizeChoice($chartType, self::CHART_TYPES, 'total-trend'),
            'theme'           => self::sanitizeChoice($theme, self::CHART_THEMES, 'slate'),
            'width'           => self::clampInt($width, 640, 4096, 1000),
            'height'          => self::clampInt($height, 240, 2160, 360),
            'zerobaseline'    => (bool) $zeroBase,
            'yticks'          => self::clampInt($yTicks, 2, 12, 6),
            'xlabeleverydays' => self::clampInt($xLabelDays, 0, 365, 0),
            'showvaluelabels' => (bool) $showValues,
            'datelabelformat' => self::sanitizeChoice($dateFormat, self::CHART_DATE_LABEL_FORMATS, 'yyyy-mm-dd'),
            'showgeneratedat' => (bool) $showGen,
            'titlemode'       => self::sanitizeChoice($titleMode, self::CHART_TITLE_MODES, 'default'),
            'titletext'       => mb_substr(trim((string) $titleText), 0, 120),
            'chartalt'        => mb_substr(trim((string) $chartAlt), 0, 200),
            'chartdata'       => (string) $chartData,
        ];

        $chartUrl = self::buildChartUrl($owner, $repo, $chartOptions);
        $chartAltText = $chartOptions['chartalt'] !== ''
            ? $chartOptions['chartalt']
            : $owner . '/' . $repo . ' downloads (' . $chartOptions['charttype'] . ')';
        $chartMd  = '![' . $chartAltText . '](' . $chartUrl . ')';

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
                'type'  => $chartOptions['charttype'],
                'theme' => $chartOptions['theme'],
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

        $options = self::chartOptionsFromRequest($input);

        $app->setHeader('Content-Type', 'image/svg+xml; charset=utf-8', true);

        $series = [];
        $generatedAt = gmdate('Y-m-d\TH:i:s\Z');

        if (trim($options['chartdata']) !== '') {
            // Manual override wins over the published snapshots.
            $series = self::normalizeSeries(self::parseChartData($options['chartdata']));
        } else {
            $data = self::fetchStats($options['provider'], $options['owner'], $options['repo'], $options['branch'], $options['jsonpath']);

            if ($data !== null) {
                $series = self::extractSeries($data);

                if (isset($data['generatedAt']) && is_string($data['generatedAt']) && $data['generatedAt'] !== '') {
                    $generatedAt = $data['generatedAt'];
                }
            }
        }

        return self::buildChartSvg($series, [
            'owner'           => $options['owner'],
            'repo'            => $options['repo'],
            'generatedAt'     => $generatedAt,
            'chartType'       => $options['charttype'],
            'chartTheme'      => $options['theme'],
            'width'           => $options['width'],
            'height'          => $options['height'],
            'zeroBaseline'    => $options['zerobaseline'],
            'yTicks'          => $options['yticks'],
            'xLabelEveryDays' => $options['xlabeleverydays'],
            'showValueLabels' => $options['showvaluelabels'],
            'dateLabelFormat' => $options['datelabelformat'],
            'showGeneratedAt' => $options['showgeneratedat'],
            'titleMode'       => $options['titlemode'],
            'titleText'       => $options['titletext'],
            'altText'         => $options['chartalt'],
        ]);
    }
}
