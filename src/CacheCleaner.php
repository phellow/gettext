<?php
namespace Phellow\Gettext;

/**
 * Helps to update gettext translations.
 *
 * This class cleans the gettext cache by changing the domain.
 * There must be a file like locale.mo to copy it to a file
 * like locale_[timestamp].mo. It creates a file like domain.txt
 * in the locale directory that holds the current used domain
 * (e.g. locale_123456789). PHP always have to check which domain
 * exists in the domain.txt so it knows which domain to load for gettext.
 *
 * @author    Christian Blos <christian.blos@gmx.de>
 * @copyright Copyright (c) 2014-2015, Christian Blos
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/phellow/gettext
 */
class CacheCleaner
{

    /**
     * @var string
     */
    protected $localeDir = null;

    /**
     * @var string
     */
    protected $domain = 'locale';

    /**
     * @var string
     */
    protected $domainFilename = 'domain.txt';

    /**
     * @param string $localeDir Path to locale dir (where language folders are placed).
     */
    public function __construct($localeDir)
    {
        $this->localeDir = $localeDir;
    }

    /**
     * @param string $domain
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Set the name of the file that holds the current domain.
     *
     * @param string $domainFilename
     *
     * @return void
     */
    public function setDomainFilename($domainFilename)
    {
        $this->domainFilename = $domainFilename;
    }

    /**
     * @param string $log
     *
     * @return void
     */
    protected function log($log)
    {
        echo $log . PHP_EOL;
    }

    /**
     * @param string $domain
     *
     * @return void.
     */
    protected function saveDomain($domain)
    {
        $domainFile = $this->localeDir . DIRECTORY_SEPARATOR . $this->domainFilename;

        if (file_put_contents($domainFile, $domain)) {
            $this->log('saved new domain to ' . $domainFile);
        } else {
            throw new \Exception('failed to save new domain to ' . $domainFile);
        }
    }

    /**
     * Update mo files and update gettext domain.
     *
     * @return string
     */
    public function execute()
    {
        if (!is_dir($this->localeDir)) {
            throw new \Exception('locale dir is not a directory (' . $this->localeDir . ')');
        }

        $poFiles = [];
        $domain = $this->domain . '_' . time();

        foreach (scandir($this->localeDir) as $file) {
            $langDir = $this->localeDir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($langDir) && !preg_match('/^\./', $file)) {
                $moDir = $langDir . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR;
                $moFile = $moDir . $this->domain . '.mo';
                $poFiles[$file] = $moDir . $this->domain . '.po';

                //remove old created mo files
                foreach (scandir($moDir) as $f) {
                    if (preg_match('/^' . $this->domain . '_[0-9]+\.mo$/', $f)) {
                        $f = $moDir . $f;
                        if (unlink($f)) {
                            $this->log('removed old file: ' . $f);
                        } else {
                            $this->log('failed to remove old file ' . $f);
                        }
                    }
                }

                if (file_exists($moFile)) {
                    $newFile = $moDir . $domain . '.mo';
                    if (copy($moFile, $newFile)) {
                        $this->log('created file: ' . $newFile);
                    } else {
                        throw new \Exception('failed to create file: ' . $newFile);
                    }
                }
            }
        }

        $this->saveDomain($domain);

        return $domain;
    }
}