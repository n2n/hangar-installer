<?php
namespace n2n\composer\skeleton;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Composer;
use n2n\composer\module\ComponentInstaller;

class ComponentPlugin implements PluginInterface {
	
	public function activate(Composer $composer, IOInterface $io) {
		$installer = new ComponentInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);
	}
}