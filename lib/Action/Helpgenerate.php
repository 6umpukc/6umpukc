<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

//TODO!!!

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
		$destPath = dirname($this->script) . '/.dev/docs/';
		if (!is_dir($destPath))
		{
			mkdir($destPath, 0777, true);
		}

		return $destPath . 'COMMANDS.md';
	}

	public function getDocTitle()
	{
		return '# Список команд утилиты bx' . "\n\n";
	}

	public function getDocCommandDescription($name, $command)
	{
		// $command['class'];

		$title = '# bx ' . mb_strtolower($name) . "\n\n";

		return $title . $command['descr'] . "\n";
	}

	public function run()
	{
		$destFilePath = $this->getDestPath();

		file_put_contents($destFilePath, $this->getDocTitle());

		foreach ($this->getCommands() as $name => $command)
		{
			file_put_contents($destFilePath, $this->getDocCommandDescription($name, $command), FILE_APPEND);
		}

		echo 'Generated to ' . $destFilePath . "\n";
	}
}

