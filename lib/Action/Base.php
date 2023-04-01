<?php

namespace Rodzeta\Siteoptions\Action;

use DirectoryIterator;

class Base
{
	protected $script;
	protected $siteRootPath;
	protected $params;

	public function __construct($script = null, $siteRootPath = null, $params = [])
	{
		$this->script = $script;
		$this->siteRootPath = $siteRootPath ?: getcwd();
		$this->params = $params;
	}

	public function getName()
	{
		return mb_strtolower(array_pop(explode('\\', static::class)));
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

	// system helpers

	protected function getUser()
	{
		return $_SERVER['USER'] ?? '';
	}

	protected function getRealBinPath()
	{
		return dirname($_SERVER['argv'][0]);
	}

	protected function getRandomPassword()
	{
		$result = '';

		$length = 17;
		$characters = '0123456789'
			. 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = mb_strlen($characters);
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		$length = 3;
		$characters = '!_-.,|';
		$charactersLength = mb_strlen($characters);
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $result;
	}

	protected function getRandomName()
	{
		$length = 9;
		$characters = '0123456789'
			. 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = mb_strlen($characters);
		$result = 'usr';
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $result;
	}

	protected function confirm($title)
	{
		$line = readline("$title Type 'yes' to continue: ");

		return trim($line) == 'yes';
	}

	// site helpers

	protected function getPublicPrefix()
	{
		return $_SERVER['DIR_PUBLIC'] ?? '';
	}

	protected function getPublicPath()
	{
		$prefix = $this->getPublicPrefix();
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

	protected function getCommands()
	{
		$result = [
			'install' => [
				'descr' => 'bx install - Установка bx в домашней директории пользователя',
			],

			'install-php' => [
				'descr' => 'bx php [version] - Установка php интерпретатора',
			],

			'install-lamp' => [
				'descr' => 'bx lamp - Установка LAMP',
			],

			'env' => [
				'descr' => 'bx env - Вывод переменных окружения по проекту',
			],

			'ftp' => [
				'descr' => 'bx ftp - Подключится по ftp (через filezilla)',
			],

			'ssh' => [
				'descr' => 'bx ssh - Подключится по ssh',
			],

			'putty' => [
				'descr' => 'bx putty - Подключится по ssh через putty',
			],

			'start' => [
				'descr' => 'bx start - Запустить сервисы LAMP',
			],

			'stop' => [
				'descr' => 'bx stop - Остановить сервисы LAMP',
			],
		];

		foreach (new DirectoryIterator(__DIR__) as $f)
		{
			if (!$f->isFile())
			{
				continue;
			}

			$originalName = $f->getBasename('.php');
			$name = mb_strtolower($originalName);
			if (in_array($name, [
					'base',
					'help',
				]))
			{
				continue;
			}

			$className = __NAMESPACE__ . '\\' . $originalName;
			if (!class_exists($className))
			{
				continue;
			}

			$action = $this->create($className);

			$result[$action->getName()] = [
				'class' => __NAMESPACE__ . '\\' . $originalName,
				'descr' => $action->getDescription(),
			];
		}

		ksort($result);

		return $result;
	}
}
