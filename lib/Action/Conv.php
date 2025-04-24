<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Encoding;

final class Conv extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' utf|win . - Конвертирует кодировку в текущей директории' . "\n"
			. 'bx ' . $this->getName() . ' [utf]|win - Конвертирует кодировку модулей решения';
	}

	protected function processDir($basePath, $encoding, $onlyExtensions = null)
	{
		if ($encoding == 'win')
		{
			echo "Converting to windows-1251 $basePath ...\n";
		}
		else
		{
			echo "Converting to UTF-8 $basePath ...\n";
		}

		foreach (Encoding::getList($basePath, [
				'/xml/ru/',
			], $onlyExtensions) as $f)
		{
			$name = $f->getPathname();
			$content = file_get_contents($name);
			$isUtf = Encoding::isUtf($content);

			if ($encoding == 'win')
			{
				if ($isUtf)
				{
					echo $name . "\n";
					$content = mb_convert_encoding($content, 'windows-1251', 'utf-8');
					file_put_contents($name, $content);
				}
				continue;
			}

			if (!$isUtf)
			{
				echo $name . "\n";
				$content = mb_convert_encoding($content, 'utf-8', 'windows-1251');
				file_put_contents($name, $content);
			}
		}
	}

	public function run()
	{
		$encoding = $this->params[0] ?? 'utf';
		$onlyCurrentDir = !empty($this->params[1]) && ($this->params[1] == '.');

		$onlyExtensions = array_map('trim', array_filter(explode(',', $this->params[2] ?? '')));
		if (count($onlyExtensions) == 0) {
			$onlyExtensions = null;
		}

		if ($onlyCurrentDir)
		{
			$basePath = getcwd();
			$this->processDir($basePath, $encoding, $onlyExtensions);

			return;
		}

		foreach ($this->git->iterateRepos() as $repoInfo)
		{
			$this->processDir($repoInfo['path'], $encoding, $onlyExtensions);
		}
	}
}

