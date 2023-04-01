<?php

namespace Rodzeta\Siteoptions\Action;

final class Fixdir extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Поправить права в папке проекта';
	}

	public function run()
	{
		$dirUser = $_SERVER['SITE_DIR_USER'] ?? '';
		if ($dirUser != '')
		{
			system("sudo chown -R $dirUser {$this->siteRootPath}");
		}

		$dirRights = $_SERVER['SITE_DIR_RIGHTS'] ?? '';
		if ($dirRights != '')
		{
			system("sudo chmod -R $dirRights {$this->siteRootPath}");
		}
	}
}
