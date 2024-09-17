<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Stop extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Остановить сервисы LAMP и т.д.';
	}

	public function run()
	{
		if (Shell::isUbuntu())
		{
			Shell::service('stop', 'apache2');
			Shell::service('stop', 'mysql');

			if (Shell::checkCommand('rinetd')) {
				Shell::service('stop', 'rinetd');
			}
		}
		else
		{
			Shell::service('stop', 'httpd.service');
			Shell::service('stop', 'mysqld.service');
		}
	}
}
