<?php

namespace Rodzeta\Siteoptions\Action;

use DirectoryIterator;
use CModule;
use CIBlock;
use CIBlockType;
use Bitrix\Main\Data\StaticHtmlCache;
use Bitrix\Main\Data\ManagedCache;
use CStackCacheManager;
use CHTMLPagesCache;
use CEventMessage;
use CEventType;
use Bitrix\Main\Application;
use Rodzeta\Siteoptions\Base;
use Rodzeta\Siteoptions\Shell;

final class Solutionreset extends Base
{
	public function getName()
	{
		return 'solution-reset';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Удаляет файлы и таблицы БД решения';
	}

	public function run()
	{
		if (!Shell::confirm('Warning! Site public data will be removed.'))
		{
			return;
		}

		$this->create(Fixdir::class)->run();

		$this->clearSite();
		$this->clearFiles();

		$this->clearIblock();
		$this->clearMailTemplates();
		$this->clearTables();

		$this->clearCache();
	}

	protected function clearMailTemplates()
	{
		if (empty($_SERVER['SOLUTION_MAIL_EVENT_PREFIX']))
		{
			return;
		}

		$emailTypePrefixes = array_filter(array_map('trim', explode("\n", trim($_SERVER['SOLUTION_MAIL_EVENT_PREFIX']))));
		echo "Remove email templates\n";
		// remove solution email templates and types
		$emsg = new CEventMessage();
		$res = CEventMessage::GetList($by = 'id', $order = 'desc', $arFilter = []);
		while ($row = $res->GetNext())
		{
			foreach ($emailTypePrefixes as $emailTypePrefix)
			{
				if ($emailTypePrefix == substr($row['EVENT_NAME'], 0, strlen($emailTypePrefix)))
				{
					echo "\t" . $row['ID'] . ' - ' . $row['EVENT_NAME'] . "...\n";
					$emsg->Delete($row['ID']);
				}
			}
		}
		echo "Remove email types\n";
		$et = new CEventType();
		$res = CEventType::GetList();
		while ($row = $res->Fetch())
		{
			foreach ($emailTypePrefixes as $emailTypePrefix)
			{
				if ($emailTypePrefix == substr($row['EVENT_NAME'], 0, strlen($emailTypePrefix)))
				{
					echo "\t" . $row['ID'] . ' - ' . $row['EVENT_NAME'] . "...\n";
					$et->Delete($row['EVENT_NAME']);
				}
			}
		}
	}

	protected function clearTables()
	{
		if (empty($_SERVER['SOLUTION_DB_PREFIX']))
		{
			return;
		}

		$dbTablePrefixes = array_filter(array_map('trim', explode("\n", trim($_SERVER['SOLUTION_DB_PREFIX']))));
		echo "Remove database tables\n";
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		foreach ($connection->query('show tables from ' . $sqlHelper->forSql($connection->getDatabase())) as $row)
		{
			$tableName = current($row);
			foreach ($dbTablePrefixes as $dbTablePrefix)
			{
				if ($dbTablePrefix == substr($tableName, 0, strlen($dbTablePrefix)))
				{
					echo "\t" . $tableName . "...\n";
					$connection->dropTable($tableName);
				}
			}
		}
	}

	protected function fixPath($path)
	{
		return str_replace(DIRECTORY_SEPARATOR, '/', $path);
	}

	protected function clearSite()
	{
		$basePath = $this->siteRootPath;

		$excludedDirs = [
			'bitrix' => 1,
			'local' => 1,
			'upload' => 1,
		];
		$excludedFiles = [
			'.access.php' => 1,
			'.htaccess' => 1,
		];
		echo "Cleanup site files\n";
		$it = new DirectoryIterator($basePath);
		foreach ($it as $f)
		{
			$name = $f->getPathname();
			if ($f->isDir())
			{
				if (!$f->isDot() && !isset($excludedDirs[$f->getBasename()]))
				{
					echo "\tremove " . $name . " ...\n";
					system('rm -Rf ' . $name);
				}
			}
			elseif ($f->isFile())
			{
				if (!isset($excludedFiles[$f->getBasename()]))
				{
					echo "\tremove " . $name . "\n";
					unlink($name);
				}
			}
		}
	}

	protected function clearFiles()
	{
		$basePath = $this->siteRootPath;

		if (empty($_SERVER['SOLUTION_PREFIX']))
		{
			echo "Solution prefix not defined - skip files cleanup\n";
			return;
		}
		$prefix = $_SERVER['SOLUTION_PREFIX'];
		$dirs = [
			'bitrix/wizards',
			'bitrix/components',
			'bitrix/templates',
			'bitrix/js',
			'bitrix/tools',
			'bitrix/admin',
		];
		foreach ($dirs as $dir)
		{
			$path = $this->fixPath($basePath . DIRECTORY_SEPARATOR . $dir);
			if (!is_dir($path))
			{
				continue;
			}
			echo 'Cleanup ' . $path . " ...\n";

			$it = new DirectoryIterator($path);
			foreach ($it as $f)
			{
				$name = $f->getPathname();
				if (strpos(basename($name), $prefix) === false)
				{
					continue;
				}

				if ($f->isDir())
				{
					echo "\tremove dir " . $name . " ...\n";
					system('rm -Rf ' . $name);
				}
				else
				{
					echo "\tremove file " . $name . " ...\n";
					unlink($name);
				}
			}
		}
	}

	protected function clearIblock()
	{
		echo "Remove iblocks\n";
		CModule::IncludeModule('iblock');
		$res = CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N'], true);
		while ($row = $res->Fetch())
		{
			echo "\t" . $row['ID'] . ' - ' . $row['CODE'] . "...\n";
			CIBlock::Delete($row['ID']);
		}
		echo "Remove iblock types\n";
		$res = CIBlockType::GetList();
		while ($row = $res->Fetch())
		{
			echo "\t" . $row['ID'] . "...\n";
			CIBlockType::Delete($row['ID']);
		}
	}

	protected function clearCache()
	{
		echo "Clear cache...\n";
		BXClearCache(true);

		if (class_exists(StaticHtmlCache::class, false))
		{
			echo "Clear static html cache...\n";
			StaticHtmlCache::getInstance()->deleteAll();
		}
		if (class_exists(ManagedCache::class, false))
		{
			echo "Clear managed cache...\n";
			$cache = new ManagedCache();
			$cache->cleanAll();
		}
		if (class_exists(CStackCacheManager::class, false))
		{
			echo "Clear stack cache...\n";
			$cache = new CStackCacheManager();
			$cache->CleanAll();
		}
		if (method_exists(CHTMLPagesCache::class, 'CleanAll'))
		{
			echo "Clear htmlpages cache...\n";
			CHTMLPagesCache::CleanAll();
		}
	}
}