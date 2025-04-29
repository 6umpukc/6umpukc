<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Helpgenerate extends Base
{
	public function getName()
	{
		return 'help-generate';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Генерация документации по командам в COMMANDS.md';
	}

	public function getDestPath()
	{
		$destPath = dirname($this->script);
		if (!is_dir($destPath))
		{
			mkdir($destPath, 0777, true);
		}

		return $destPath . '/COMMANDS.md';
	}

	public function getDocTitle()
	{
		return '# Список команд утилиты bx' . "\n\n";
	}

	public function getDocCommandDescription($name, $command)
	{
		$tmp = explode("\n", $command['descr']);
		$title = array_shift($tmp);
		if (trim($title) == '')
		{
			return '';
		}

		$command = 'bx ' . mb_strtolower($name);

		return '## ' . $title . "\n\n"
			. '`' . $command . '`' . "\n\n";
	}

	public function run()
	{
		$destFilePath = $this->getDestPath();

		file_put_contents($destFilePath, $this->getDocTitle());

		foreach ($this->getCommands() as $name => $command)
		{
			$commandDescription = $this->getDocCommandDescription($name, $command);
			if ($commandDescription == '')
			{
				continue;
			}
			file_put_contents($destFilePath, $commandDescription, FILE_APPEND);
		}

		echo 'Generated to ' . $destFilePath . "\n";
	}
}

