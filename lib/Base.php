<?php

namespace Rodzeta\Siteoptions;

use DirectoryIterator;
use Rodzeta\Siteoptions\Git;
use Rodzeta\Siteoptions\Shell;
use Rodzeta\Siteoptions\Config;

class Base
{
	protected $name;
	protected $script;
	protected $siteRootPath;
	protected $params;

	protected $git;

	public function __construct($script = null, $siteRootPath = null, $params = [], $name = null)
	{
		if ($name !== null)
		{
			$this->name = $name;
		}

		$this->script = $script;
		$this->siteRootPath = $siteRootPath ?: getcwd();
		$this->params = $params;

		$this->git = new Git($this->siteRootPath, [
			'SOLUTION_GIT_REPOS' => $_SERVER['SOLUTION_GIT_REPOS'] ?? '',
		]);
	}

	public function getName()
	{
		$chunks = explode('\\', static::class);

		return mb_strtolower(array_pop($chunks));
	}

	public function getDescription()
	{
		return '';
	}

	public function run()
	{
	}

	public function create($className)
	{
		return new $className(
			$this->script,
			$this->siteRootPath,
			$this->params
		);
	}

	protected function getCommands()
	{
		$result = [];

		foreach ([
				new DirectoryIterator(__DIR__ . '/Action'),
				new DirectoryIterator(__DIR__ . '/Action/Override')
			] as $dirIterator)
		{
			foreach ($dirIterator as $f)
			{
				if (!$f->isFile())
				{
					continue;
				}
				if ($f->getExtension() != 'php')
				{
					continue;
				}

				$originalName = $f->getBasename('.php');
				$name = mb_strtolower($originalName);
				if (in_array($name, [
						'help',
					]))
				{
					continue;
				}

				$className = __NAMESPACE__ . '\\Action\\Override\\' . $originalName;
				if (!class_exists($className))
				{
					$className = __NAMESPACE__ . '\\Action\\' . $originalName;
					if (!class_exists($className))
					{
						continue;
					}
				}

				$action = $this->create($className);

				$result[$action->getName()] = [
					'class' => $className,
					'descr' => $action->getDescription(),
				];
			}
		}

		ksort($result);

		return $result;
	}

	// site helpers

	protected function getPublicPath()
	{
		$prefix = Config::getPublicPrefix();
		if (mb_substr($prefix, -1) == '/')
		{
			$prefix = mb_substr($prefix, 0, mb_strlen($prefix) - 1);
		}

		return $this->siteRootPath . $prefix;
	}

	protected function getSiteHost()
	{
		$siteDir = $this->siteRootPath;
		$siteHost = basename($siteDir);
		if ($siteHost == 'public')
		{
			$siteDir = dirname($siteDir);
			$siteHost = basename($siteDir);
		}
		if ($siteHost == 'app')
		{
			$siteDir = dirname($siteDir);
			$siteHost = basename($siteDir);
		}

		return $siteHost;
	}

	protected function getSiteConfig()
	{
		$sitehost = $this->getSiteHost();
		return '/etc/apache2/sites-available/' . $sitehost . '.conf';
	}

	protected function patchSiteConfig($destpath, $newContent)
	{
		echo "\n";
		echo "# Apache2 site config -> $destpath\n";
		echo "\n";
		echo $newContent . "\n";

		$tmp = $this->siteRootPath . '/.newsiteconfig.tmp';
		file_put_contents($tmp, $newContent);

		system("sudo mv $tmp $destpath");
		system("sudo service apache2 reload");
	}

	protected function getDevPath($path = '')
	{
		return dirname($this->script) . '/.dev/' . $path;
	}
}
