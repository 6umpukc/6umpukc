<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Vpn extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Подключится по VPN';
	}

	public function run()
	{
		if (empty($_SERVER['VPN_HOST']))
		{
			return;
		}

		$host = $_SERVER['VPN_HOST'];
		$username = $_SERVER['VPN_USER'];
		$password = $_SERVER['VPN_PASSWORD'];

		$home = Shell\Win::getEnvVariable('USERPROFILE');
		$script = $home . '/bin/bx_cisco_autologin.js';

		Shell\Win::cscript([
			$script,
			$host,
			$username,
			$password,
		]);
	}
}
