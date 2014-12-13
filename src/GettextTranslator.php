<?php
namespace Phellow\Gettext;

use Phellow\Intl\TranslatorInterface;

// fix for windows.
if (!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 6);
}

/**
 * Translator that uses gettext.
 *
 * @author    Christian Blos <christian.blos@gmx.de>
 * @copyright Copyright (c) 2014-2015, Christian Blos
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/phellow/gettext
 */
class GettextTranslator implements TranslatorInterface
{

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $localeDir;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @param string $localeDir Path to gettext locale files.
     * @param string $domain    Gettext domain.
     */
    public function __construct($localeDir, $domain = null)
    {
        $this->localeDir = $localeDir;
        $this->setDomain($domain);
    }

    /**
     * @param string $domain
     *
     * @return void
     */
    public function setDomain($domain)
    {
        bindtextdomain($domain, $this->localeDir);
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);
    }

    /**
     * Set mapping for locales.
     *
     * This is useful for systems where there is no locale installed
     * for a specific language. For example your language is 'de' but
     * only 'de_DE.utf8' is installed. Than you need the following mapping:
     * $mapping = [
     *   'de' => 'de_DE.utf8',
     * ];
     *
     * @param array $mapping
     *
     * @return void
     */
    public function setLocaleMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Ensure that correct locale is set.
     *
     * Info: The priority order of constants that gettext uses is:
     * - LANGUAGE
     * - LC_ALL
     * - LC_MESSAGES
     * - LANG
     *
     * @param string $locale
     *
     * @return void
     */
    protected function ensureLocale($locale)
    {
        if (isset($this->mapping[$locale])) {
            $locale = $this->mapping[$locale];
        }

        if ($this->locale != $locale) {
            putenv('LANGUAGE=' . $locale);
            putenv('LC_MESSAGES=' . $locale);
            setlocale(\LC_MESSAGES, $locale);
            $this->locale = $locale;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function translate($text, $locale)
    {
        $this->ensureLocale($locale);
        return gettext($text);
    }

    /**
     * {@inheritdoc}
     */
    public function translatePlurals($textSingular, $textPlural, $number, $locale)
    {
        $this->ensureLocale($locale);
        return ngettext($textSingular, $textPlural, $number);
    }
}
