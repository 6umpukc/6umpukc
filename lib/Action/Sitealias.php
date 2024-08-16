<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\ConfigPatcher;
use Rodzeta\Siteoptions\Config;

final class Sitealias extends ConfigPatcher
{
	public function getName()
	{
		return 'site-alias';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' [alias] - установить ServerAlias в конфиге хоста

Пример с использованием cloudflared для Local WP:

	cloudflared tunnel --url http://localhost:10005/

порт можно посмотреть по информации сайта: http://your-localwp.site/local-phpinfo.php

	$_SERVER["SERVER_PORT"]	-> 10005

Пример с использованием cloudflared для WSL / Ubuntu и т. д.:

	cloudflared tunnel --url http://mysite1.dev/

Указать полученый домен как alias

---

https://developers.cloudflare.com/pages/how-to/preview-with-cloudflare-tunnel/

https://github.com/cloudflare/cloudflared
';
	}

	public function processSiteConfig(&$originalContent)
	{
		$alias = $this->params[0] ?? '';

		$content = "\n";
		if ($alias != '')
		{
			$content = '
ServerAlias ' . $alias . '
';
		}

		$originalContent = Config::replaceBlock('alias', $content, $originalContent);
	}
}
