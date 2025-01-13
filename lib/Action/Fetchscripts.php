<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Fetchscripts extends Base
{
	public function getName()
	{
		return 'fetch-scripts';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Скачать служебные скрипты bitrix (bitrixsetup.php, restore.php и т. д.)';
	}
}


