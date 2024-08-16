<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Sitehosts extends Base
{
	public function getName()
	{
		return 'site-hosts';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Добавить в /etc/hosts домен проекта';
	}

	public function run()
	{
		$localIp = '127.0.0.1';
		$localIpDup = '::1';
		$sitehost = $this->getSiteHost();
		$etcHostsPath = '/etc/hosts';
		$etcHostsPathWin = '';

		if (Shell::isWSL())
		{
			$etcHostsPathWin = Shell::getWinEnvVariable('systemroot') . '\\system32\\drivers\\etc\\hosts';
			$etcHostsPath = Shell::convertToWinPath($etcHostsPathWin);
		}

		$hosts = explode("\n", file_get_contents($etcHostsPath));

		echo "Hosts path: " . $etcHostsPath . "\n";

		$newHosts = [];
		foreach ($hosts as $line)
		{
			if (mb_strpos($line, $sitehost) === false)
			{
				$newHosts[] = $line;
			}
		}

		echo "ADD to config:\n";
		echo "\n";
		$line = $localIp . "\t" . $sitehost;
		echo "$line\n";
		$newHosts[] = $line;
		$line = $localIpDup . "\t" . $sitehost;
		echo "$line\n";
		$newHosts[] = $line;

		echo "\n";

		if (Shell::isWSL())
		{
			//TODO!!! как добавлять в системный файл windows автоматизировано
			echo "NOTE: for WSL add lines to $etcHostsPathWin manually\n\n";
			return;
		}

		$tmp = $this->siteRootPath . '/.hosts.tmp';
		file_put_contents($tmp, implode("\n", $newHosts) . "\n");

		system("sudo mv $tmp " . $etcHostsPath);

		echo "\n";
	}
}