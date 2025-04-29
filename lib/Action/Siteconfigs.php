<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;
use DirectoryIterator;

final class Siteconfigs extends Base
{
	protected $mainDirs;
	protected $destBasePath;

	public function getName()
	{
		return 'site-configs';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Скопировать настройки сайтов' . "\n"
			. Shell::getDisplayEnvVariable('BX_SITE_CONFIG_DEST')
			. Shell::getDisplayEnvVariable('BX_SITE_CONFIG_SRC');
	}

	public function __construct($script = null, $siteRootPath = null, $params = [])
	{
		parent::__construct($script, $siteRootPath, $params);

		$this->destBasePath = Shell::getReplacedEnvVariables($_SERVER['BX_SITE_CONFIG_DEST']);
		$this->mainDirs = array_map(
			fn ($v) => Shell::getReplacedEnvVariables(trim($v)),
			explode("\n", trim($_SERVER['BX_SITE_CONFIG_SRC']))
		);
	}

	protected function expandPaths($paths)
	{
		$paths = array_flip($paths);
		$result = $paths;
		foreach ($paths as $path => $_)
		{
			if (!is_dir($path))
			{
				continue;
			}
			foreach (new DirectoryIterator($path) as $f)
			{
				if ($f->isDot())
				{
					continue;
				}
				if ($f->isDir())
				{
					foreach (new DirectoryIterator($f->getPathname()) as $ff)
					{
						if ($ff->isDot())
						{
							continue;
						}
						if ($ff->isDir())
						{
							continue;
						}
						if (substr($ff->getBasename(), 0, 4) == '.env')
						{
							$result[dirname($ff->getPathname())] = 1;
						}
					}
				}
			}
		}

		return array_keys($result);
	}

	protected function removeMainDir($path, $mainDirs)
	{
		$result = $path;
		foreach ($mainDirs as $basePath)
		{
			if (mb_strpos($path, $basePath) === 0)
			{
				$result = mb_substr($path, mb_strlen($basePath));
				break;
			}
		}

		return $result;
	}

	public function run()
	{
		$mainDirs = $paths = $this->mainDirs;

		$destBasePath = $this->destBasePath;

		$paths = $this->expandPaths($paths);

		foreach ($paths as $path)
		{
			if (!is_dir($path))
			{
				continue;
			}
			foreach (new DirectoryIterator($path) as $fileInfo)
			{
				if ($fileInfo->isDot())
				{
					continue;
				}
				if (!$fileInfo->isDir())
				{
					continue;
				}
				$checkPaths = [
					$fileInfo->getPathname(),
					$fileInfo->getPathname() . '/.vscode/',
				];
				if (is_dir($fileInfo->getPathname() . '/app/'))
				{
					$checkPaths[] = $fileInfo->getPathname() . '/app/';
				}
				if (is_dir($fileInfo->getPathname() . '/app/public/'))
				{
					$checkPaths[] = $fileInfo->getPathname() . '/app/public/';
					$checkPaths[] = $fileInfo->getPathname() . '/app/public/.vscode/';
				}
				foreach ($checkPaths as $checkPath)
				{
					if (!is_dir($checkPath))
					{
						continue;
					}
					foreach (new DirectoryIterator($checkPath) as $f)
					{
						if (!$f->isFile())
						{
							continue;
						}

						if ((substr($f->getBasename(), 0, 4) == '.env')
							|| ($f->getBasename() == '__debug.php.log')
							|| ($f->getExtension() == 'code-workspace')
							|| ($f->getExtension() == 'json'))
						{
							echo 'Copy ' . basename($path) . ' / ' . $fileInfo->getBasename() . ': ' . $f->getPathname() . "\n";
							$destPath = $destBasePath . $this->removeMainDir($f->getPathname(), $mainDirs);
							$destDir = dirname($destPath);

							if (!is_dir($destDir))
							{
								mkdir($destDir, 0777, true);
							}
							copy($f->getPathname(), $destPath);
						}
					}
				}
			}
		}

		chdir($destBasePath);
		echo 'Changes on ' . $destBasePath . "\n";
		system('git status');

		$this->makeArchive();
	}

	protected function makeArchive()
	{
		$destBasePath = $this->destBasePath;

		$destPath = Shell::getReplacedEnvVariables($_SERVER['BX_BACKUP_GIT_REPOS_DEST'] ?? '');
		$destPathArchived = Shell::getReplacedEnvVariables(
			$_SERVER['BX_BACKUP_GIT_REPOS_DEST_ARCHIVED'] ?? ($destPath . '/.archived/'));

		echo "\n" . $destPathArchived . "\n";

		$archivePath = $destPathArchived . '_tmpenv.tar';
		Shell::tarCreate($archivePath, $destBasePath);
	}
}
