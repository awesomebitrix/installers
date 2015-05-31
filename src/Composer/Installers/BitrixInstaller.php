<?php
namespace Composer\Installers;

use Composer\Util\Filesystem;

/**
 * Installer for Bitrix Framework
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class BitrixInstaller extends BaseInstaller
{
    protected $locations = array(
        'module'    => 'bitrix/modules/{$name}/',
        'component' => 'bitrix/components/{$name}/',
        'theme'     => 'bitrix/templates/{$name}/',
    );

    protected static $isInstall = 1;

    protected function templatePath($path, array $vars = array())
    {
        $templatePath = parent::templatePath($path, $vars);
        $this->checkDuplicates($templatePath, $vars);

        return $templatePath;
    }

    /**
     * Duplicates search packages
     *
     * @param string $templatePath
     * @param array $vars
     */
    protected function checkDuplicates($templatePath, array $vars = array())
    {
        if (static::$isInstall === 0) {
            return;
        }

        static::$isInstall--;

        /**
         * Incorrect paths for backward compatibility
         */
        $oldLocations = array(
            'module'    => 'local/modules/{$name}/',
            'component' => 'local/components/{$name}/',
            'theme'     => 'local/templates/{$name}/'
        );

        $packageType = substr($vars['type'], strlen('bitrix') + 1);
        $oldLocation = str_replace('{$name}', $vars['name'], $oldLocations[$packageType]);

        if ($oldLocation !== $templatePath && file_exists($oldLocation)) {

            $this->io->writeError('    <error>Duplication of packages:</error>');
            $this->io->writeError('    <info>Package ' . $oldLocation . ' will be called instead package ' . $templatePath . '</info>');

            while (true) {
                switch ($this->io->ask('    <info>Delete ' . $oldLocation . ' [y,n,?]?</info> ', '?')) {
                    case 'y':
                        $fs = new Filesystem();
                        $fs->removeDirectory($oldLocation);
                        break 2;

                    case 'n':
                        break 2;

                    case '?':
                    default:
                        $this->io->writeError([
                            '    y - delete package ' . $oldLocation . ' and to continue with the installation',
                            '    n - don\'t delete and to continue with the installation',
                        ]);
                        $this->io->writeError('    ? - print help');
                        break;
                }
            }
        }
    }
}
