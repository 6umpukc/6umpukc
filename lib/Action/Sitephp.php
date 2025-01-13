<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\ConfigPatcher;
use Rodzeta\Siteoptions\Config;

final class Sitephp extends ConfigPatcher
{
	public function getName()
	{
		return 'site-php';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [version] - Сменить версию php для сайта проекта';
	}

	protected function processSiteConfig(&$originalContent)
	{
		$version = $this->params[0] ?? '';

		$content = "\n";
		if ($version != '')
		{
			system("sudo service php$version-fpm start");

			$content = '
<FilesMatch \.php$>
	SetHandler "proxy:unix:/var/run/php/php' . $version . '-fpm.sock|fcgi://localhost/"
	#SetHandler "proxy:unix:/run/php/php' . $version . '-fpm.sock|fcgi://localhost"
</FilesMatch>
';
		}

		$originalContent = Config::replaceBlock('php', $content, $originalContent);
	}
}