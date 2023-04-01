<?php

namespace Rodzeta\Siteoptions\Action;

final class Siteremove extends Base
{
	public function getName()
	{
		return 'site-remove';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Удалить сайт (конфиги вебсервера и БД)';
	}

	public function run()
	{
		$dbpassword = $_SERVER['DB_PASSWORD'] ?? '';
		$dbname = $_SERVER['DB_DATABASE'] ?? '';
		if ($dbname == '')
		{
			return;
		}

		$this->create(Sitereset::class)->run();

		$sitehost = $this->getSiteHost();
		$destpath = $this->getSiteConfig();

		system("sudo a2dissite $sitehost.conf");
		system("sudo rm $destpath");
		system('sudo apache2 reload');

		// remove db
		$dbconf = $this->getRealBinPath() . '/.template/ubuntu/dbdrop.sql';
		$sqlContent = file_get_contents($dbconf);
		$sqlContent = str_replace('bitrixdb1', $dbname, $sqlContent);
		$sqlContent = str_replace('bitrixuser1', $dbname, $sqlContent);
		$sqlContent = str_replace('bitrixpassword1', $dbpassword, $sqlContent);

		$dbconf = $this->siteRootPath . '/.dbdrop.tmp.sql';
		file_put_contents($dbconf, $sqlContent);
		system("sudo mysql -u root < '$dbconf'");
		unlink($dbconf);
	}
}