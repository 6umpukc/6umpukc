<?php

namespace Rodzeta\Siteoptions\Action;

final class Sitephp extends Base
{
	public function getName()
	{
		return 'site-php';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [version] - Сменить версию php для проекта';
	}

	public function run()
	{
		$destpath = $this->getSiteConfig();
		$version = $this->params[0] ?? '';

		$originalContent = file_get_contents($destpath);

		// patch content
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

		$re = '{\#bx\-php\s+start\s.+?\#bx\-php\s+end}s';
		if (preg_match($re, $originalContent))
		{
			// replace
			$originalContent = preg_replace(
				$re,
				"#bx-php start$content#bx-php end",
				$originalContent
			);
		}
		else
		{
			// add
			$originalContent = str_replace(
				'</VirtualHost>',
				"\n#bx-php start$content#bx-php end\n\n</VirtualHost>",
				$originalContent
			);
		}

		if (trim($content) == '')
		{
			$originalContent = str_replace(
				"#bx-php start$content#bx-php end",
				'',
				$originalContent
			);
		}

		// remove empty lines
		$originalContent = preg_replace(
			"{\n{2,}}si",
			"\n\n",
			$originalContent
		);

		$this->patchSiteConfig($destpath, $originalContent);
	}
}