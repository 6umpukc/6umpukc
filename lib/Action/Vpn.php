<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Vpn extends Base
{
	public function getName()
	{
		return 'vpn';
	}

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

		$home = Shell::getWinEnvVariable('USERPROFILE');
		$script = $home . '/bin/win_cisco_autologin.js';

		ob_start();
		Shell::run(
			'cscript.exe "' . $script . '"'
			. ' "' . $host . '"'
			. ' "' . $username . '"'
			. ' "' . $password . '"'
			. ' 2>/dev/null'
		);
		ob_end_clean();
	}
}
