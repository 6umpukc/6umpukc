<?php

namespace Rodzeta\Siteoptions\Action;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

final class Siteinit extends Base
{
	public function getName()
	{
		return 'site-init';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Создать сайт для проекта (конфиги вебсервера и БД) - выполнять из папки проекта';
	}

	public function addSite($siteconf)
	{
		$publicPath = $this->getPublicPath();
		$currentUser = $this->getUser();
		$sitehost = $this->getSiteHost();

		$siteconf = $this->getRealBinPath() . '/.template/ubuntu/' . $siteconf;

		$content = file_get_contents($siteconf);
		$content = str_replace('/home/user/ext_www/bitrix-site.com', $publicPath, $content);
		$content = str_replace('bitrix-site.com', $sitehost, $content);
		$content = str_replace('/home/user/.ssl/', "/home/$currentUser/.ssl/", $content);

		$siteconf = $this->siteRootPath . '/.apache2.conf.tmp';
		file_put_contents($siteconf, $content);

		$destpath = $this->getSiteConfig();

		echo "\n";
		echo "# Apache2 site config -> $destpath\n";
		echo "\n";
		echo "$content\n";

		system("sudo mv $siteconf $destpath");
		system("sudo a2ensite {$sitehost}.conf");
		system('sudo service apache2 reload');
	}

	protected function addDatabase($dbconf, $dbname, $dbpassword)
	{
		$dbconf = $this->getRealBinPath() . '/.template/ubuntu/' . $dbconf;
		$sqlContent = file_get_contents($dbconf);

		$sqlContent = str_replace('bitrixdb1', $dbname, $sqlContent);
		$sqlContent = str_replace('bitrixuser1', $dbname, $sqlContent);
		$sqlContent = str_replace('bitrixpassword1', $dbpassword, $sqlContent);

		$dbconf = $this->siteRootPath . '/.dbcreate.tmp.sql';
		file_put_contents($dbconf, $sqlContent);

		system("sudo mysql -u root < '$dbconf'");
		unlink($dbconf);
	}

	protected function copySiteFiles($siteExists, $encoding, $dbname, $dbpassword)
	{
		$templatePath = $this->getRealBinPath() . '/.template/www';

		$srcDirs = [
			$templatePath,
		];
		if ($encoding != 'win')
		{
			$srcDirs[] = $templatePath . '_' . $encoding;
		}
		foreach ($srcDirs as $templatePath)
		{
			$it = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$templatePath,
					RecursiveDirectoryIterator::SKIP_DOTS
				)
			);
			foreach ($it as $f)
			{
				$path = $f->getPathname();
				if (!$f->isFile())
				{
					continue;
				}

				// skip .env
				if ($siteExists)
				{
					if (mb_strpos(basename($path), '.env') !== false)
					{
						continue;
					}
				}

				$dest = $this->siteRootPath	. str_replace($templatePath, '', $path);
				$destDir = dirname($dest);
				if (!is_dir($destDir))
				{
					mkdir($destDir, octdec($_SERVER['SITE_DIR_RIGHTS']), true);
				}
				copy($path, $dest);

				if ((mb_strpos($dest, '/adminer/') !== false)
					|| (mb_strpos($dest, '/local/modules/') !== false))
				{
					continue;
				}

				// fix settings
				$envContent = file_get_contents($dest);
				$envContent = str_replace('dbname1', $dbname, $envContent);
				$envContent = str_replace('dbuser1', $dbname, $envContent);
				$envContent = str_replace('dbpassword12345', $dbpassword, $envContent);
				$envContent = str_replace('111encoding111', $encoding, $envContent);
				$envContent = str_replace(
					'http://bitrixsolution01.example.org/',
					'https://' . $this->getSiteHost() . '/',
					$envContent
				);
				file_put_contents($dest, $envContent);
			}
		}
	}

	public function run()
	{
		$siteExists = (($_SERVER['SITE_ENCODING'] ?? '') != '');
		$encoding = $this->params[0] ?? 'win'; // win | utf | utflegacy

		$this->create(Fixdir::class)->run();

		$dbpassword = $this->getRandomPassword();
		$dbname = $this->getRandomName();
		if ($siteExists)
		{
			$encoding = $_SERVER['SITE_ENCODING'] ?? '';
			$dbpassword = $_SERVER['DB_PASSWORD'] ?? '';
			$dbname = $_SERVER['DB_DATABASE'] ?? '';
		}
		else
		{
			//TODO!!! для local - указать стандартные параметры БД для localwp

			$siteconf = '';
			$dbconf = '';
			if ($encoding == 'win')
			{
				$siteconf = 'win1251site.conf';
				$dbconf = 'win1251dbcreate.sql';
			}
			else if ($encoding == 'utflegacy')
			{
				$siteconf = 'utf8legacysite.conf';
				$dbconf = 'utf8dbcreate.sql';
				$encoding = 'utf';
			}
			else
			{
				$siteconf = 'utf8site.conf';
				$dbconf = 'utf8dbcreate.sql';
				$encoding = 'utf';
			}

			// init site conf files
			$this->addSite($siteconf);
			$this->addDatabase($dbconf, $dbname, $dbpassword);
		}

		$this->copySiteFiles($siteExists, $encoding, $dbname, $dbpassword);
	}
}
