<?php

namespace Rodzeta\Siteoptions;

use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Config;

abstract class ConfigPatcher extends Base
{
	abstract protected function processSiteConfig(&$originalContent);

	public function run()
	{
		$destPath = $this->getSiteConfig();

		$originalContent = file_get_contents($destPath);

		$this->processSiteConfig($originalContent);

		$originalContent = Config::removeEmptyLines($originalContent);

		$this->patchSiteConfig($destPath, $originalContent);
	}
}
