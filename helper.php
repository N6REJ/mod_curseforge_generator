<?php

/**
 * @package     Bearsampp.Module.CfdgGenerator
 * @subpackage  mod_cfdg_generator
 * @license     MIT License
 * @link        https://github.com/Bearsampp/mod_cfdg_generator
 */

defined('_JEXEC') or die;

class ModCfdgGeneratorHelper
{
    /**
     * Resolve the GitHub organisation/user that owns the repositories.
     * Accepts "https://github.com/Bearsampp", "github.com/Bearsampp", "Bearsampp" or
     * "Bearsampp/mod_cfdg_generator" (only the first path segment is used).
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

        return $owner !== '' ? $owner : 'Bearsampp';
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
}
