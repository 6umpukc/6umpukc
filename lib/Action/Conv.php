<?php

namespace Rodzeta\Siteoptions\Action;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

final class Conv extends Base
{
	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' win|utf - Конвертирует кодировку файлов в текущей директории';
	}

	public function run()
	{
		/* TODO!!!

		//TODO!!! use native conv
action_conv_win([basePath = '']) async {
  return run_php([REAL_BIN + '/.action_conv.php', 'win']);
}

//TODO!!! use native conv
action_conv_utf([basePath = '']) async {
  var args = [REAL_BIN + '/.action_conv.php', 'utf'];
  if (basePath != '') {
    args.add(get_public_path(basePath));
  }

  return run_php(args);
}

action_solution_conv_utf(basePath) async {
  require_site_root(basePath);

  var solutionRepos = git_repos_map(basePath);
  if (solutionRepos.keys.length == 0) {
    return;
  }

  for (final moduleId in solutionRepos.keys) {
    var repoInfo = solutionRepos[moduleId];
    var page = repoInfo[0];
    var url = repoInfo[1];
    var branch = repoInfo[2];
    var path = repoInfo[3];

    if (!Directory(path).existsSync()) {
      print("Directory '$path' for '$url' not exists");
      continue;
    }
    chdir(path);
    await runInteractive('pwd', []);
    await action_conv_utf();
    print('');
  }
}

		*/

		$encoding = $_SERVER['argv'][3] ?? '';
		$fname = $_SERVER['argv'][4] ?? '';
		switch ($encoding)
		{
			case 'utf':
				(($fname == '') || !file_exists($fname) || is_dir($fname))?
					$this->convUtfAction()
					: $this->convUtfFileAction($fname);
				break;
			case 'win':
				$this->convWinAction();
				break;
			default:
				break;
		}
	}

	protected function convUtfFileAction($name)
	{
		$content = file_get_contents($name);
		if (mb_detect_encoding($content, 'UTF-8', true))
		{
			return;
		}
		$content = mb_convert_encoding($content, 'utf-8', 'windows-1251');
		file_put_contents($name, $content);
	}

	protected function convUtfAction()
	{
		$basePath = getcwd();
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$basePath,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $f)
		{
			$name = $f->getPathname();
			if (strpos($name, '.git') !== false
					|| strpos($name, '.dev') !== false
					|| strpos($name, '/xml/ru/') !== false
					|| strpos($name, 'vendor') !== false)
			{
				continue;
			}
			if ($f->isDir())
			{
				//echo "processing $name ...\n";
				continue;
			}
			if (!$f->isFile())
			{
				continue;
			}
			$ext = $f->getExtension();
			if ($ext != 'php' && $ext != 'xml' && $ext != 'json')
			{
				continue;
			}
			$content = file_get_contents($name);
			if (mb_detect_encoding($content, 'UTF-8', true))
			{
				// utf
				continue;
			}
			echo $name . "\n";
			$content = mb_convert_encoding($content, 'utf-8', 'windows-1251');
			file_put_contents($name, $content);
		}
	}

	protected function convWinAction()
	{
		$basePath = getcwd();
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$basePath,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $f)
		{
			$name = $f->getPathname();
			if (strpos($name, '.git') !== false
					|| strpos($name, '.dev') !== false
					|| strpos($name, '/xml/ru/') !== false
					|| strpos($name, 'vendor') !== false)
			{
				continue;
			}
			if ($f->isDir())
			{
				//echo "processing $name ...\n";
				continue;
			}
			if (!$f->isFile())
			{
				continue;
			}
			$ext = $f->getExtension();
			if ($ext != 'php' && $ext != 'xml' && $ext != 'json')
			{
				continue;
			}
			$content = file_get_contents($name);
			if (!mb_detect_encoding($content, 'UTF-8', true))
			{
				// not utf
				continue;
			}
			echo $name . "\n";
			$content = mb_convert_encoding($content, 'windows-1251', 'utf-8');
			file_put_contents($name, $content);
		}
	}

}

