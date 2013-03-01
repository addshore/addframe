#!/usr/bin/perl -w
 
# Code : Dake
use strict;
use Parse::MediaWikiDump;
use utf8;
 
my $file = shift(@ARGV) or die "must specify a Mediawiki dump file";
my $pages = Parse::MediaWikiDump::Pages->new($file);
my $page;
 
binmode STDOUT, ":utf8";
 
while(defined($page = $pages->next)) {
    #main namespace only
    next unless $page->namespace eq '';
 
    my $text = $page->text;
    if (($$text =~ /\[\[de:/i) && ($$text =~ /\[\[es:/i) &&
        ($$text =~ /\[\[nl:/i) && ($$text =~ /\[\[ja:/i) &&
        ($$text =~ /\[\[it:/i) && !($$text =~ /\[\[fr:/i))
     {
         print $page->title, "\n";
     }
}