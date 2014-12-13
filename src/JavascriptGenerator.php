<?php
namespace Phellow\Gettext;

/**
 * Generator for javascript gettext file.
 *
 * @author    Christian Blos <christian.blos@gmx.de>
 * @copyright Copyright (c) 2014-2015, Christian Blos
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/phellow/gettext
 */
class JavascriptGenerator
{

    /**
     * @var string
     */
    protected $javascriptTemplate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $objectName = 'locale';

    /**
     * @var string
     */
    protected $translateFunctionName = '_';

    /**
     * @var string
     */
    protected $translatePluralsFunctionName = '_n';

    /**
     * @var string
     */
    protected $pluralForms = 'nplurals=2; plural=(n != 1)';

    /**
     * The markup [var] will be replaced with the replacement key.
     *
     * @var string
     */
    protected $replacementRegex = '\$\{[var]\}';

    /**
     * @param string $javascriptTemplate Template or null to use default template.
     */
    public function __construct($javascriptTemplate = null)
    {
        if ($javascriptTemplate === null) {
            $this->javascriptTemplate = __DIR__ . '/../tpl/locale.template.js';
        } else {
            $this->javascriptTemplate = $javascriptTemplate;
        }
    }

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $objectName
     *
     * @return void
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     * @param string $translateFunctionName
     *
     * @return void
     */
    public function setTranslateFunctionName($translateFunctionName)
    {
        $this->translateFunctionName = $translateFunctionName;
    }

    /**
     * @param string $translatePluralsFunctionName
     *
     * @return void
     */
    public function setTranslatePluralsFunctionName($translatePluralsFunctionName)
    {
        $this->translatePluralsFunctionName = $translatePluralsFunctionName;
    }

    /**
     * @param string $pluralForms
     *
     * @return void
     */
    public function setPluralForms($pluralForms)
    {
        $this->pluralForms = $pluralForms;
    }

    /**
     * @param string $replacementRegex
     *
     * @return void
     */
    public function setReplacementRegex($replacementRegex)
    {
        $this->replacementRegex = $replacementRegex;
    }

    /**
     * @param string $poFile
     *
     * @return string The javascript code.
     */
    public function generateFromPo($poFile)
    {
        if (!file_exists($poFile)) {
            throw new \Exception('po file does not exist (' . $poFile . ')');
        }

        $locale = [];
        $key = null;
        $pluralForms = null;

        $f = fopen($poFile, 'r');
        while ($line = fgets($f)) {
            $matches = [];
            $line = trim($line);

            if (preg_match('/^msgid "(.*)"$/', $line, $matches)) {
                $key = $matches[1];
            } elseif (preg_match('/^msgstr "(.*)"$/', $line, $matches)) {
                $locale[$key] = $matches[1];
            } elseif (preg_match('/^msgid_plural "(.*)"$/', $line, $matches)) {
                $locale[$key] = [];
            } elseif (preg_match('/^msgstr\[([0-9])\] "(.*)"$/', $line, $matches)) {
                $subkey = (int)$matches[1];
                $locale[$key][$subkey] = $matches[2];
            } elseif (preg_match('/^"Plural-Forms: (.*)\\\n"$/', $line, $matches)) {
                $pluralForms = $matches[1];
            }
        }

        //clear texts that have no translations
        foreach ($locale as $k => $v) {
            if (!$k || !$v) {
                unset($locale[$k]);
            }
            if (is_array($v)) {
                foreach ($v as $t) {
                    if (!$t) {
                        unset($locale[$k]);
                        break;
                    }
                }
            }
        }

        $locale = json_encode($locale);
        $locale = $locale ?: '{}';

        if (!file_exists($this->javascriptTemplate)) {
            throw new \Exception('template file does not exist (' . $this->javascriptTemplate . ')');
        }

        $content = file_get_contents($this->javascriptTemplate);
        $content = str_replace('__LOCALE_CODE__', $this->locale ? "'" . $this->locale . "'" : 'null', $content);
        $content = str_replace('__OBJECT_NAME__', $this->objectName, $content);
        $content = str_replace('__LOCALE_DATA__', $locale, $content);
        $content = str_replace('__REPLACEMENT_REGEX__', addslashes($this->replacementRegex), $content);
        $content = str_replace('__TRANSLATE_FUNCTION_NAME__', $this->translateFunctionName, $content);
        $content = str_replace('__TRANSLATE_PLURALS_FUNCTION_NAME__', $this->translatePluralsFunctionName, $content);
        $content = str_replace('__PLURAL_FORMS__', $pluralForms ?: $this->pluralForms, $content);

        return $content;
    }

    /**
     * Convert po file to javascript translation file.
     *
     * The javascript file will be overwritten if it exists.
     *
     * @param string $poFile
     * @param string $jsFile
     *
     * @return bool
     */
    public function create($poFile, $jsFile)
    {
        return file_put_contents($jsFile, $this->generateFromPo($poFile));
    }
}