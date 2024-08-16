<?php

namespace Rodzeta\Siteoptions\Action;

use Rodzeta\Siteoptions\Base;

final class Modupdate extends Base
{
	public function getName()
	{
		return 'mod-update';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Обновить версию модуля, cоздать коммит и запушить тег обновления (выполнять из папки модуля)';
	}

	public function run()
	{
		//TODO!!! проверять наличие изменений с последней версии - если их нет не менять версию

		//TODO!!! version param
		$refreshVersion = !empty($_SERVER['argv'][2]) && ($_SERVER['argv'][2] == 'refresh');

		$basePath = getcwd();
		$fname = $basePath . '/install/version.php';
		require $fname;

		$arModuleVersion['VERSION_DATE'] = date('Y-m-d H:i:s');
		$version = explode('.', trim($arModuleVersion['VERSION']));
		if (!$refreshVersion)
		{
			$v = (int)array_pop($version) + 1;
			$version[] = $v;
		}
		$strVersion = implode('.', $version);
		$arModuleVersion['VERSION'] = $strVersion;

		file_put_contents($fname, '<' . '?php
		$arModuleVersion = array(
			"VERSION" => "' . $strVersion . '",
			"VERSION_DATE" => "' . $arModuleVersion['VERSION_DATE'] . '",
		);');

		if ($refreshVersion)
		{
			system('git tag -d ' . $strVersion);
			system('git push origin master :' . $strVersion);
		}
		system('git commit -am "Номер версии"');
		system('git tag ' . $strVersion);
		system('git push origin ' . $strVersion);
		system('git push');

		//TODO!!! url solution from config SOLUTION_GIT_REPOS
		//if (!empty($_SERVER['argv'][1]))
		//{
		//	system('xdg-open "' . $_SERVER['argv'][1] . '"');
		//}
	}
}