
package X::Utils;

use 5.016;
use warnings;
use base 'Exporter';

sub trim {
	my $s = shift;
	$s =~ s/^\s+|\s+$//g;
	return $s;
}

sub unquote {
	my $s = shift;
	my $start = substr($s, 0, 1);
	my $end = substr($s, -1, 1);
	while ((length($s) > 1) && ($start eq $end) && (($start eq '"') || ($start eq "'")))
	{
		$s = substr($s, 1, -1);
		$start = substr($s, 0, 1);
		$end = substr($s, -1, 1);
	}
	return $s;
}

our @EXPORT = qw(
	trim
	unquote
);

1;
