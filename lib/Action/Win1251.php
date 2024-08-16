<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Encoding;

final class Win1251 extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' Проверить наличие файлов кодировке Windows-1251 в текущей директории';
	}

	public function run()
	{
		$basePath = getcwd();

		foreach (Encoding::getList($basePath, [
				'/xml/ru/',
				'/lang/ru/',
				'/public/ru/',
				'/LICENSE.php',
			]) as $f)
		{
			$content = file_get_contents($f->getPathname());

			if ($f->getExtension() == 'php')
			{
				$content = Encoding::getWithoutComments($content);
			}

			if (!Encoding::isUtf($content))
			{
				echo $f->getPathname() . "\n";
			}
		}
	}
}
