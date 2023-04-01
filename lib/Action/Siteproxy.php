<?php

namespace Rodzeta\Siteoptions\Action;

final class Siteproxy extends Base
{
	public function getName()
	{
		return 'site-proxy';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [host] - Установить IP для прокси-сайта';
	}

	public function run()
	{
		$destpath = $this->getSiteConfig();
		$ip = $this->params[0] ?? '';

		$originalContent = file_get_contents($destpath);

		// patch content
		$content = "\n";
		if ($ip != '')
		{
			$content = '
ProxyPreserveHost On
ProxyPass        /  http://' . $ip . '/
ProxyPassReverse /  http://' . $ip . '/
';
		}

		$re = '{\#bx\-proxy\s+start\s.+?\#bx\-proxy\s+end}s';
		if (preg_match($re, $originalContent))
		{
			// replace
			$originalContent = preg_replace(
				$re,
				"#bx-proxy start$content#bx-proxy end",
				$originalContent
			);
		}
		else
		{
			// add
			$originalContent = str_replace(
				'</VirtualHost>',
				"\n#bx-proxy start$content#bx-proxy end\n\n</VirtualHost>",
				$originalContent
			);
		}

		if (trim($content) == '')
		{
			$originalContent = str_replace(
				"#bx-proxy start$content#bx-proxy end",
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
