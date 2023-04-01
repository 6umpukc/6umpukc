<?php

namespace Rodzeta\Siteoptions\Action;

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
		$hosts = explode("\n", file_get_contents('/etc/hosts'));

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

		$tmp = $this->siteRootPath . '/.hosts.tmp';
		file_put_contents($tmp, implode("\n", $newHosts) . "\n");
		system("sudo mv $tmp /etc/hosts");

		echo "\n";
  		echo 'NOTE: for WSL add lines to "%systemroot%\\system32\\drivers\\etc\\hosts" manually' . "\n";
		//TODO!!!
	}
}