<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\ConfigPatcher;
use Rodzeta\Siteoptions\Shell;
use Rodzeta\Siteoptions\Config;

final class Sitehttps extends ConfigPatcher
{
	public function getName()
	{
		return 'site-https';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' on|off - Включить/отключить https для сайта';
	}

	protected function processSiteConfig(&$originalContent)
	{
		$certKeyName = $_SERVER['BX_MKCERT'];
		if ($certKeyName == '')
		{
			$certKeyName = 'bx.local *.bx.local';
		}
		$tmp = explode(' ', $certKeyName);
		$certKeyName = $tmp[0];

		$enableHttps = (count($this->params) && ($this->params[0] == 'off'))? false : true;

		$sslPath = Shell::getHome() . '/.ssl/' . $certKeyName
			. '+' . (count($tmp) - 1);
		$sslCertPath = "SSLCertificateFile $sslPath.pem";
		$sslCertKeyPath = "SSLCertificateKeyFile $sslPath-key.pem";

		$httpsBegin = [
			'<IfModule mod_ssl.c>',
			'<VirtualHost *:443>',
		];
		$httpsEnd = [
			'</VirtualHost>',
			'</IfModule>',
		];

		$httpBegin = [
			'<VirtualHost *:80>',
		];
		$httpEnd = [
			'</VirtualHost>',
		];

		if ($enableHttps)
		{
			$originalContent = Config::replaceContent($httpBegin, $httpsBegin, $originalContent, $count);

			$content = "
SSLEngine on
$sslCertPath
$sslCertKeyPath
";
			if ($count)
			{
				$originalContent = Config::replaceContent($httpEnd, $httpsEnd, $originalContent);
			}
		}
		else
		{
			$originalContent = Config::replaceContent($httpsBegin, $httpBegin, $originalContent);

			$content = "\n";

			$originalContent = Config::replaceContent($httpsEnd, $httpEnd, $originalContent);
		}

		$originalContent = Config::replaceBlock('https', $content, $originalContent);
	}
}
