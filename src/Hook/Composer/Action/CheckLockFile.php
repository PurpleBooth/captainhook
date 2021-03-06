<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianFeldmann\CaptainHook\Hook\Composer\Action;

use SebastianFeldmann\CaptainHook\Config;
use SebastianFeldmann\CaptainHook\Console\IO;
use SebastianFeldmann\CaptainHook\Hook\Action;
use SebastianFeldmann\Git\Repository;

/**
 * Class CheckLockFile
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/captainhook
 * @since   Class available since Release 1.0.1
 */
class CheckLockFile implements Action
{
    /**
     * Executes the action.
     *
     * @param  \SebastianFeldmann\CaptainHook\Config         $config
     * @param  \SebastianFeldmann\CaptainHook\Console\IO     $io
     * @param  \SebastianFeldmann\Git\Repository             $repository
     * @param  \SebastianFeldmann\CaptainHook\Config\Action  $action
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action)
    {
        $path           = $action->getOptions()->get('path', getcwd());
        $lockFileHash   = $this->getLockFileHash($path);
        $configFileHash = $this->getConfigFileHash($path);

        if ($lockFileHash !== $configFileHash) {
            throw new \Exception('composer.lock is out of date');
        }

        $io->write('<info>composer.lock is up to date</info>');
    }

    /**
     * Read the composer.lock file and extract the composer.json hash.
     *
     * @param  string $path
     * @return string
     */
    private function getLockFileHash(string $path) : string
    {
        $lockFile = json_decode($this->loadFile($path . DIRECTORY_SEPARATOR . 'composer.lock'));

        return $lockFile->hash;
    }

    /**
     * Read the composer.json file and create a md5 hash on its contents.
     *
     * @param  string $path
     * @return string
     */
    private function getConfigFileHash(string $path) : string
    {
        return md5($this->loadFile($path . DIRECTORY_SEPARATOR . 'composer.json'));
    }

    /**
     * Load a composer file.
     *
     * @param  string $file
     * @return string
     * @throws \Exception
     */
    private function loadFile(string $file) : string
    {
        if (!file_exists($file)) {
            throw new \Exception($file . ' not found');
        }
        return file_get_contents($file);
    }
}
