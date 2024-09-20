<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Db extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Открыть adminer.php';
	}

	public function run()
	{
		if (!empty($_SERVER['APP_URL']))
		{
			$url = $_SERVER['APP_URL'];
		}
		else
		{
			//TODO http or https from settings
			$url = 'http://' . $this->getSiteHost() . '/';
		}

		$url .= 'adminer/?' . http_build_query([
			'username' => $_SERVER['DB_USERNAME'],
			'db' => $_SERVER['DB_DATABASE'],
			'password' => $_SERVER['DB_PASSWORD'],
		]);

		if (Shell::isWSL())
		{
			Shell\Win::run('start "' . $url . '"', $output);
		}
		else
		{
			Shell::run('xdg-open "' . $url . '"');
		}
	}
}
