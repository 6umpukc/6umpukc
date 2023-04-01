<?php

namespace Rodzeta\Siteoptions\Action;

final class Solutioninit extends Base
{
	public function getName()
	{
		return 'solution-init';
	}

	public function getDescription()
	{
		return 'bx ' . $this->getName() . ' - Клонирует список модулей решения';
	}

	public function run()
	{
		throw new \Exception('ACTION NOT IMPLEMENTED...');
		/* TODO!!!

		var solution = (ARGV.length > 1) ? ARGV[1] : '';
		var solutionConfigPath = get_home() + '/bin/.dev/solution.env.settings/' + solution + '/example.env';
		if (solution != '') {
			if (!File(solutionConfigPath).existsSync()) {
			die("Config for solution [$solution] not defined.");
			}
			var siteConfig = basePath + '/' + BASE_ENV_NAME;
			var originalContent = file_get_contents(siteConfig);
			var content = file_get_contents(solutionConfigPath);
			if (originalContent.indexOf(content) < 0) {
			content = originalContent + "\n" + content + "\n";
			file_put_contents(siteConfig, content);
			}
			ENV_LOCAL = await load_env(siteConfig);
		}
		await fetch_repos(basePath);

		//TODO!!! добавлять в .gitignore в корне сайта список путей для каждого репозитария

		*/
	}
}