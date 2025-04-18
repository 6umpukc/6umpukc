
package X::File;

use 5.016;
use warnings;
use base 'Exporter';
#use Exporter qw(import);

sub get {
	my $filename = shift;
	my $mode = shift;
	if (!defined($mode)) {
		$mode = '<:encoding(UTF-8)';
	}

	my $result = '';
	if (open(my $fh, $mode, $filename)) {
		while (my $line = <$fh>) {
			$result .= $line;
		}
	}
	return $result;
}

sub put {
	my $filename = shift;
	my $content = shift;
	my $mode = shift;
	if (!defined($mode)) {
		$mode = '>:encoding(UTF-8)';
	}

	if (open(my $fh, $mode, $filename)) {
		print $fh $content;
		close $fh;
		return 1;
	}
	return 0;
}

sub file_get_contents {
	my $filename = shift;

	return get($filename);
}

sub file_get_contents_win {
	my $filename = shift;

	return get($filename, '<:encoding(Windows-1251)');
}

sub file_put_contents {
	my $filename = shift;
	my $content = shift;

	return put($filename, $content);
}

sub file_put_contents_win {
	my $filename = shift;
	my $content = shift;

	return put($filename, $content, '>:encoding(Windows-1251)');
}

sub require_file {
	my $fname = shift;
	my $info = shift || '';
	if (!-f $fname) {
		die($info . ' [' . $fname . '] file - not found.');
	}
}

our @EXPORT = qw(
	file_get_contents
	file_get_contents_win
	file_put_contents
	file_put_contents_win
	require_file
);

1;
