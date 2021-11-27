<?php

$_SERVER['DOCUMENT_ROOT'] = $_SERVER['argv'][1];

function Action_clear_site_and_bitrix($basePath)
{
	$excludedDirs = [
		'local' => 1,
	];
	echo "Cleanup site files\n";
	$it = new \DirectoryIterator($basePath);
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
			echo "\tremove " . $name . "\n";
			unlink($name);
		}
	}
}

function Action_clear_db_tables($basePath)
{
	$mysqli = new mysqli('127.0.0.1', $_SERVER['DB_USERNAME'], $_SERVER['DB_PASSWORD'], $_SERVER['DB_DATABASE']);
	if ($mysqli->connect_errno)
	{
		die(
			"Can't connect to database " .  $_SERVER['DB_DATABASE'] . " with user " . $_SERVER['DB_USERNAME'] . "\n"
			. "\t" . $mysqli->connect_errno . "\n"
    		. "\t" . $mysqli->connect_error . "\n"
    	);
	}

	echo "Remove site db tables\n";
	$res = $mysqli->query('show tables from ' . $mysqli->real_escape_string($_SERVER['DB_DATABASE']));
	if ($res)
	{
		while ($row = $res->fetch_assoc())
		{
			$tableName = current($row);
			echo "\t" . $tableName . "...\n";

			$resRemoveTable = $mysqli->query('drop table ' . $mysqli->real_escape_string($tableName));
			if (!$resRemoveTable)
			{
				echo "DB error: " . $mysqli->errno . ", " . $mysqli->error . "\n";
			}
		}
		$res->free();
	}
	else
	{
		echo "DB error: " . $mysqli->errno . ", " . $mysqli->error . "\n";
	}
	$mysqli->close();


}

$basePath = $_SERVER['DOCUMENT_ROOT'];

Action_clear_site_and_bitrix($basePath);
Action_clear_db_tables($basePath);
