<?php
namespace n2n\composer\skeleton\component;

use Composer\Installer\LibraryInstaller;
use Composer\Package\Package;

class ComponentInstaller extends LibraryInstaller {
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::supports()
	 */
	public function supports($packageType) {
		return $packageType == self::N2N_SKELETON_COMPONENT_TYPE;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::install()
	 */
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::install($repo, $package);
		
		$this->removeResources($package);
		$this->installResources($package);
	}
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::update()
	 */
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial,
			\Composer\Package\PackageInterface $target) {
				
		$this->moveBackResources($initial);
		
		parent::update($repo, $initial, $target);
		
		$this->removeResources($target);
		$this->installResources($target);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::uninstall()
	 */
	public function uninstall(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		$this->moveBackResources($package);
		
		parent::uninstall($repo, $package);
	}
	
	const N2N_SKELETON_COMPONENT_TYPE = 'n2n-skeleton-component';
	const TARGET_ORIG_DIR = 'target';
	const DEST_DIR = '..' . DIRECTORY_SEPARATOR . 'comp';
	
	private function getModuleName(Package $package) {
		return pathinfo($this->getInstallPath($package), PATHINFO_BASENAME);
	}
	
	private function getOrigDirPath(Package $package) {
		return $this->filesystem->normalizePath($this->getInstallPath($package) . DIRECTORY_SEPARATOR
				. self::TARGET_ORIG_DIR);
	}
	
	private function getDestDirPath() {
		return $this->filesystem->normalizePath($this->vendorDir . DIRECTORY_SEPARATOR . self::DEST_DIR) ;
	}
	
	private function getRelDirPath(Package $package) {
		return DIRECTORY_SEPARATOR . $this->getModuleName($package);
	}
	
	public function moveBackResources(Package $package) {
		$relDirPath = $this->getRelDirPath($package);
		$componentOrigDirPath = $this->getOrigDirPath($package) . $relDirPath;
		$componentDestDirPath = $this->getDestDirPath() . $relDirPath;
		
		if (is_dir($componentDestDirPath)) {
			$this->filesystem->copyThenRemove($componentDestDirPath, $componentOrigDirPath);
		}
	}
	
	private function removeResources(Package $package) {
		$destDirPath = $this->getDestDirPath() . $this->getRelDirPath($package);
		if (is_dir($destDirPath)) {
			try {
				$this->filesystem->removeDirectory($destDirPath);
			} catch (\RuntimeException $e) {}
		}
	}
	
	private function installResources(Package $package) {
		$this->moveComponent($package);
	}
	
	private function moveComponent(Package $package) {
		$origDirPath = $this->getOrigDirPath($package);
		$destDirPath = $this->getDestDirPath();
		
		$this->valOrigDirPath($origDirPath, $package);
		
		$relDirPath = $this->getRelDirPath($package);
		$componentOrigDirPath = $origDirPath . $relDirPath;
		$componentDestDirPath = $destDirPath . $relDirPath;
		
		if (!is_dir($componentOrigDirPath)) {
			return;
		}
		
// 		if ($this->valDestDirPath($destDirPath, $package)) {
			$this->filesystem->copyThenRemove($componentOrigDirPath, $componentDestDirPath);
// 		}
	}
	
	private function valOrigDirPath($origDirPath, Package $package) {
		if (is_dir($origDirPath)) return;
		
		$dirName = pathinfo($origDirPath, PATHINFO_BASENAME);
		throw new CorruptedComponentException($package->getPrettyName() . ' has type \'' . self::N2N_SKELETON_COMPONENT_TYPE
				. '\' but contains no ' . $dirName . ' directory: ' . $origDirPath);
	}
	
// 	private function valDestDirPath($destDirPath, Package $package) {
// 		if (is_dir($destDirPath)) return true;
		
// 		$dirName = pathinfo($destDirPath, PATHINFO_BASENAME);
		
// 		$question = $package->getPrettyName() . ' is an ' . self::N2N_SKELETON_COMPONENT_TYPE
// 				. ' and requires a ' . $dirName . ' directory (' . $destDirPath
// 				. '). Do you want to skip the installation of the ' . $dirName . ' files? [y,n] (default: y): ';
// 		if ($this->io->askConfirmation($question)) return false;
		
// 		throw new ComponentInstallationException('Failed to install ' . self::N2N_SKELETON_COMPONENT_TYPE . ' '
// 				. $package->getPrettyName() . '. Reason: ' . $dirName . ' directory missing: ' . $destDirPath);
// 	}
	
	// 	private function copy($source, $target) {
	//         if (!is_dir($source)) {
	//             copy($source, $target);
	//             return;
	//         }
	
	//         $it = new \RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
	//         $ri = new \RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
	//         $this->ensureDirectoryExists($target);
	
	//         foreach ($ri as $file) {
	//             $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
	//             if ($file->isDir()) {
	//                 $this->filesystem->ensureDirectoryExists($targetPath);
	//             } else {
	//                 copy($file->getPathname(), $targetPath);
	//             }
	//         }
	// 	}
}