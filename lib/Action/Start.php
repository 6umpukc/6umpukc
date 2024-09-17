<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Start extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Запустить сервисы LAMP и т.д.';
	}

	public function run()
	{
		if (Shell::isUbuntu())
		{
			Shell::service('start', 'apache2');
			Shell::service('start', 'mysql');

			if (Shell::checkCommand('rinetd')) {
				Shell::service('restart', 'rinetd');
			}
		}
		else
		{
			Shell::service('start', 'httpd.service');
			Shell::service('start', 'mysqld.service');
		}
	}
}
