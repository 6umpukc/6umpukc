<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\ConfigPatcher;
use Rodzeta\Siteoptions\Config;

final class Siteproxy extends ConfigPatcher
{
	public function getName()
	{
		return 'site-proxy';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [host] - Установить IP для прокси-сайта';
	}

	protected function processSiteConfig(&$originalContent)
	{
		$ip = $this->params[0] ?? '';

		$content = "\n";
		if ($ip != '')
		{
			$content = '
ProxyPreserveHost On
ProxyPass        /  http://' . $ip . '/
ProxyPassReverse /  http://' . $ip . '/
';
		}

		$originalContent = Config::replaceBlock('proxy', $content, $originalContent);
	}
}
