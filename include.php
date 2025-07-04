<?php

//TODO!!! реализовать в виде команд
// > https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=11685&LESSON_PATH=3913.3516.4776.2483.11685
// > С версии main 24.0.0 появится возможность добавлять свои команды через файлы настроек модуля {moduleName}/.settings.php. Команды модуля перечисляются в секции console:

namespace Rodzeta\Siteoptions;

use Throwable;

// php ~/bin/6umpukc/include.php [arg1] [arg2] [arg3]

new class
{

private function initBitrixCli()
{
	// автозагрузчик от bitrix используется только при явном указании опции
	if (empty($_SERVER['BX_USE_BITRIX']))
	{
		return false;
	}

	if (file_exists($_SERVER['DOCUMENT_ROOT']
			. '/bitrix/modules/main/cli/bootstrap.php'))
	{
		try
		{
			define('BX_BUFFER_USED', true);
			require $_SERVER['DOCUMENT_ROOT']
				. '/bitrix/modules/main/cli/bootstrap.php';

			return true;
		}
		catch (Throwable $e)
		{
		}
	}

	return false;
}

private function runCommand($script, $siteRootPath, $params)
{
	$actionName = array_shift($params) ?? '';
	$originalActionName = $actionName;
	$actionName = str_replace('-', '', $actionName);
	if (empty($actionName))
	{
		return;
	}

	// check overrided or custom commands
	$className = '\\Rodzeta\\Siteoptions\\Action\\Override\\' . ucfirst($actionName);
	if (class_exists($className))
	{
		(new $className(
			$script,
			$siteRootPath,
			$params
		))->run();
		return;
	}

	// check base commands
	$className = '\\Rodzeta\\Siteoptions\\Action\\' . ucfirst($actionName);
	if (class_exists($className))
	{
		(new $className(
			$script,
			$siteRootPath,
			$params
		))->run();
		return;
	}

	// wrapper for handling undefined commands
	$className = '\\Rodzeta\\Siteoptions\\Wrapper';
	(new $className(
		$script,
		$siteRootPath,
		$params,
		$originalActionName
	))->run();
}

private function runCli()
{
	$params = $_SERVER['argv'];
	$script = array_shift($params);
	$siteRootPath = array_shift($params);
	$_SERVER['DOCUMENT_ROOT'] = $siteRootPath;

	if (empty($_SERVER['DOCUMENT_ROOT']))
	{
		$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));
	}

	if ($this->initBitrixCli())
	{
		//...
	}
	else
	{
		spl_autoload_register(function ($class) {
			$class = str_replace(__NAMESPACE__, '', $class);
			$class = str_replace('\\', '/', $class);
			$class = trim($class, '\/');

			$path = __DIR__ . '/lib/' . $class . '.php';
			if (file_exists($path))
			{
				require $path;
			}
		});
		//...
	}

	$this->runCommand($script, $siteRootPath, $params);
}

public function __construct()
{
	if (php_sapi_name() === 'cli')
	{
		$this->runCli();
	}
}

};
