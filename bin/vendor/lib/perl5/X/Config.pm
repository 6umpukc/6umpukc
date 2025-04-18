
package X::Config;

use 5.016;
use warnings;
use FindBin qw($RealBin $Bin $Script);
use Cwd qw(cwd getcwd abs_path);
use File::Basename;
use base 'Exporter';

use X::File qw(file_get_contents require_file);
use X::Utils qw(trim unquote);

sub init_php_urls {
	my $phpVersion = '8.4.2';

	$ENV{'PHP_URLS_WIN32'} = "https://windows.php.net/downloads/releases/php-$phpVersion-Win32-vs17-x86.zip";
	$ENV{'PHP_URLS_WIN64'} = "https://windows.php.net/downloads/releases/php-$phpVersion-Win32-vs17-x64.zip";
	$ENV{'PHP_URLS_MACOS'} = '';
	$ENV{'PHP_URLS_LINUX'} = '';
}

sub init_node20_urls {
	my $nodeVersion20 = 'v20.18.1';

	$ENV{'NODE_URLS_WIN32'} = "https://nodejs.org/dist/$nodeVersion20/node-$nodeVersion20-win-x86.zip";
	$ENV{'NODE_URLS_WIN64'} = "https://nodejs.org/dist/$nodeVersion20/node-$nodeVersion20-win-x64.zip";
	$ENV{'NODE_URLS_MACOS'} = "https://nodejs.org/dist/$nodeVersion20/node-$nodeVersion20-darwin-x64.tar.gz";
	$ENV{'NODE_URLS_LINUX'} = "https://nodejs.org/dist/$nodeVersion20/node-$nodeVersion20-linux-x64.tar.gz";
}

sub init_node14_urls {
	my $nodeVersion14 = 'v14.21.3';

	$ENV{'NODE14_URLS_WIN32'} = "https://nodejs.org/dist/$nodeVersion14/node-$nodeVersion14-win-x86.zip";
	$ENV{'NODE14_URLS_WIN64'} = "https://nodejs.org/dist/$nodeVersion14/node-$nodeVersion14-win-x64.zip";
	$ENV{'NODE14_URLS_MACOS'} = "https://nodejs.org/dist/$nodeVersion14/node-$nodeVersion14-darwin-x64.tar.gz";
	$ENV{'NODE14_URLS_LINUX'} = "https://nodejs.org/dist/$nodeVersion14/node-$nodeVersion14-linux-x64.tar.gz";
}

sub init_bitrix_src_urls {
	my $baseUrl = 'https://www.1c-bitrix.ru/download/';

	# distributives
	$ENV{'BITRIX_SRC_MICRO'} = $baseUrl . 'start_encode.tar.gz';
	$ENV{'BITRIX_SRC_CORE'} = $ENV{'BITRIX_SRC_MICRO'};
	$ENV{'BITRIX_SRC_START'} = $ENV{'BITRIX_SRC_MICRO'};
	$ENV{'BITRIX_SRC_BUSINESS'} = $baseUrl . 'business_encode.tar.gz';
	$ENV{'BITRIX_SRC_CRM'} = $baseUrl . 'portal/bitrix24_encode_php5.tar.gz';

	# install scripts
	$ENV{'BITRIX_SRC_SETUP'} = $baseUrl . 'scripts/bitrixsetup.php';
	$ENV{'BITRIX_SRC_RESTORE'} = $baseUrl . 'scripts/restore.php';
	$ENV{'BITRIX_SRC_TEST'} = $baseUrl . 'scripts/bitrix_server_test.php';

	# docs
	$ENV{'BITRIX_SRC_DOCS'} = '
		https://dev.1c-bitrix.ru/docs/chm_files/bsm_api.chm
		https://dev.1c-bitrix.ru/docs/chm_files/api_d7.chm
		https://dev.1c-bitrix.ru/docs/chm_files/bsm_user.chm
	';
}

sub init_default_env {
	init_php_urls();
	init_node20_urls();
	init_node14_urls();
	init_bitrix_src_urls();

	$ENV{'BX_MKCERT'} = 'bx.local *.bx.local';

	#$ENV{'DIR_PUBLIC'} = '/public/';
	$ENV{'DIR_PUBLIC'} = '/';

	#$ENV{'SITE_DIR_USER'} = 'www-data:www-data';
	$ENV{'SITE_DIR_RIGHTS'} = '0775';

	$ENV{'DIR_LOCAL_SITES'} = $ENV{'HOME'} . '/Local Sites/';
}

sub get_env_path {
	my $path = shift;
	my $envPrefix = shift;

	return $path . '/.vscode/.env'
		. (defined $envPrefix? ('.' . $envPrefix) : '');
}

sub get_env_current {
	my $basePath = shift;
	my $currentEnvPath = abs_path(get_env_path($basePath, 'local'));
	my $name = basename($currentEnvPath);
	my $currentEnvPrefix = (split(/\./, $name))[-1];

	if (! -f $currentEnvPath) {
		return 'default';
	}

	return (defined $ENV{'BX_ENV'}? $ENV{'BX_ENV'} : $currentEnvPrefix);
}

sub load_env {
	my $path = shift;
	my $disableSetEnv = shift;

	if (!-f $path) {
		return;
	}
	my %result = ();

	my $isMultiline = 0;
	my @lines = split("\n", file_get_contents($path));
	my $key = '';
	my $value = '';
	for my $line (@lines)
	{
		if ($isMultiline) {
			$value .= "\n" . $line;

			my $testLine = unquote($line);
			if ((index($testLine, "'") >= 0) || (index($testLine, '"') >= 0)) {
				$isMultiline = 0;
				$value = trim(unquote(trim($value)));
				$result{$key} = $value;
				if (!defined $disableSetEnv) {
					$ENV{$key} = $value;
				}
				$key = '';
				$value = '';
			}

			next;
		}

		$line = trim($line);
		my $p = index($line, '=');
		if ($p < 0) {
			next;
		}

		my @row = (substr($line, 0, $p), substr($line, $p + 1));
		$key = $row[0];
		if ($key eq '') {
			next;
		}
		if (substr($key, 0, 1) eq '#') {
			next;
		}

		$value = unquote($row[1]);

		if ((index($value, "'") >= 0) || (index($value, '"') >= 0)) {
			$isMultiline = 1;
			next;
		}

		$result{$key} = $value;
		if (!defined $disableSetEnv) {
			$ENV{$key} = $value;
		}
	}

	return %result;
}

our %config = ();
sub load_config {
	my $site_root = shift;

	# env common params from bx tool path
	our %config = load_env($RealBin . '/.env');

	# env common params from site
	my %configSite = load_env(get_env_path($site_root));
	for my $k (keys %configSite) {
		$config{$k} = $configSite{$k};
	}

	# env params from site BX_ENV
	my $envPrefix = '';
	if (exists $ENV{'BX_ENV'}) {
		# окружение заданое в переменной BX_ENV
		$envPrefix = $ENV{'BX_ENV'};
	} else {
		# окружение по умолчанию .env.local
		$envPrefix = 'local';
		if (!-f get_env_path($site_root, $envPrefix)) {
			$envPrefix = '';
		}
	}

	if ($envPrefix ne '') {
		my $envFname = get_env_path($site_root, $envPrefix);
		require_file($envFname, 'ENV');
		my %configBxEnv = load_env($envFname);
		for my $k (keys %configBxEnv) {
			$config{$k} = $configBxEnv{$k};
		}
	}
}

sub get_config {
	our %config;

	return %config;
}

our @EXPORT = qw(
	init_default_env
	get_env_path
	get_env_current
	load_env
	load_config
	get_config
);

1;
