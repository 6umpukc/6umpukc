<?php

namespace Rodzeta\Siteoptions\Action;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
final class Modpack extends Base
{
	public function getName()
	{
		return 'mod-pack';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Создать архив модуля для маркетплейс (выполнять из папки модуля)';
	}

	public function run()
	{
		$ignoredDirs = [
			'.dev/',
			'.git/',
			'.idea',
			'.last_version/',
			'out/',
			'src/',
		];
		$ignoredExtensions = [
			'code-workspace' => 1,
			'exe' => 1,
			'jar' => 1,
			'gitignore' => 1,
			'hxml' => 1,
			'iml' => 1,
			'sublime-project' => 1,
			'sublime-workspace' => 1,
		];
		$ignoredFiles = [
			'composer.json' => 1,
			'composer.lock' => 1,
		];
		$currentPath = getcwd();
		$dest = '.last_version.tar.gz';
		$tmpFolder = '.last_version';
		foreach ($ignoredDirs as $i => $dir)
		{
			$ignoredDirs[$i] = str_replace('/', DIRECTORY_SEPARATOR, $dir);
		}
		// clear old version
		if (is_dir($tmpFolder))
		{
			system('rm -R ' . $tmpFolder);
		}
		if (file_exists($dest))
		{
			unlink($dest);
		}
		// make full copy
		mkdir($tmpFolder);
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				'.',
				RecursiveDirectoryIterator::SKIP_DOTS
			)
		);
		foreach ($it as $f)
		{
			$name = $f->getPathname();
			if (!$f->isFile()
					|| isset($ignoredFiles[$f->getBasename()])
					|| isset($ignoredExtensions[strtolower($f->getExtension())]))
			{
				continue;
			}
			$ignored = false;
			foreach ($ignoredDirs as $dir)
			{
				if (strpos($name, $dir) !== false)
				{
					$ignored = true;
					break;
				}
			}
			if ($ignored)
			{
				continue;
			}
			// TODO!!! use mkdir and copy functions
			system('cp --verbose --parents '
				. str_replace(DIRECTORY_SEPARATOR, '/', $name) . ' ' . $tmpFolder);
		}
		unset($it);

		// convert lang files to windows-1251
		echo "\nconverting...\n";
		chdir($tmpFolder);
		(new Conv(
			$this->script,
			$this->siteRootPath,
			$this->params
		))->run();

		chdir('..');
		echo "\narchiving...\n";
		system('tar -zcvf ' . $dest . ' ' . $tmpFolder);
	}
}