<?php declare(strict_types=1);

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer;

use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryManager;
use Composer\Util\Loop;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PartialComposer
{
    /**
     * @var RootPackageInterface
     */
    private $package;

    /**
     * @var Loop
     */
    private $loop;

    /**
     * @var Repository\RepositoryManager
     */
    private $repositoryManager;

    /**
     * @var Installer\InstallationManager
     */
    private $installationManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @return RootPackageInterface
     */
    public function getPackage(): RootPackageInterface
    {
        return $this->package;
    }

    /**
     * @return void
     */
    public function setPackage(RootPackageInterface $package): void
    {
        $this->package = $package;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return void
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return Loop
     */
    public function getLoop(): Loop
    {
        return $this->loop;
    }

    /**
     * @return void
     */
    public function setLoop(Loop $loop): void
    {
        $this->loop = $loop;
    }

    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager(): RepositoryManager
    {
        return $this->repositoryManager;
    }

    /**
     * @return void
     */
    public function setRepositoryManager(RepositoryManager $manager): void
    {
        $this->repositoryManager = $manager;
    }

    /**
     * @return InstallationManager
     */
    public function getInstallationManager(): InstallationManager
    {
        return $this->installationManager;
    }

    /**
     * @return void
     */
    public function setInstallationManager(InstallationManager $manager): void
    {
        $this->installationManager = $manager;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @return void
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
