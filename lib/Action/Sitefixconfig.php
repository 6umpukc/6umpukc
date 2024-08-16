<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;
use Rodzeta\Siteoptions\Config;

final class Sitefixconfig extends Base
{
	public function getName()
	{
		return 'site-fixconfig';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - поправить конфиги для LocalWP (PHP, Apache2, MySQL)';
	}

	protected function getConfPath($siteRootPath)
	{
		$result = dirname(dirname($siteRootPath)) . '/conf/';

		if (file_exists($result . 'apache/') && file_exists($result . 'php/'))
		{
			return $result;
		}

		return '';
	}

	protected function fixSiteConf($siteConfPath)
	{
		if (!file_exists($siteConfPath))
		{
			return;
		}

		$content = file_get_contents($siteConfPath);

		$re = '{ProxySet\s+connectiontimeout=(.+?)\s+timeout=([^\n]+)}si';
		$content = preg_replace($re, 'ProxySet connectiontimeout=1200 timeout=3600', $content);

		$re = '{ProxyTimeout\s+([^\n]+)}si';
		if (preg_match($re, $content, $m))
		{
			$content = preg_replace($re, 'ProxyTimeout 1200', $content);
		}
		else
		{
			$content = str_replace('</Proxy>', '</Proxy>' . "\n\t" . 'ProxyTimeout 1200', $content);
		}

		file_put_contents($siteConfPath, $content);
	}

	public function run()
	{
		$configPath = $this->getConfPath($this->siteRootPath);
		if ($configPath == '')
		{
			return;
		}

		$phpConfPath = $configPath . 'php/php.ini.hbs';
		Shell::fixConfig($phpConfPath, Config::getTemplate('bitrix.php.ini'));
		Shell::fixConfig($phpConfPath, Config::getTemplate('local.bitrix.php.ini'));

		$mysqlConfPath = $configPath . 'mysql/my.cnf.hbs';
		Shell::fixConfig($mysqlConfPath, Config::getTemplate('local.bitrix.my.cnf'));

		$siteConfPath = $configPath . 'apache/site.conf.hbs';
		$this->fixSiteConf($siteConfPath);
	}
}
