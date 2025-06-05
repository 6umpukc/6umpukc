<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Sitelinks extends Base
{
	public function getName()
	{
		return 'site-links';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Создает символьные для сайта на bitrix ядро' . "\n"
			. Shell::getDisplayEnvVariable('BX_SITE_ROOT', true);
	}
}
