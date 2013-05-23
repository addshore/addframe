<?
/*
Grep v 1.2 © 2007-08 Nikola Smolenski <smolensk@eunet.yu>
Modified for use on wmflabs by Addshore 2013

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

require(__DIR__.'/include.php');
require(__DIR__."/../../config/replication.cfg");

head();

?><p>This tool shows all article titles that match a <a href="http://en.wikipedia.org/wiki/Regular_expression">regular expression</a> pattern.</p>
<div style="float: right;"><a href="grep.php?view_source">view source</a> <a href="include.php?view_source">view include.php source</a></div>
<form><table>
	<td align="right">Pattern:</td>
	<td><input type="text" name="pattern"<? if(isset($_GET['pattern'])) echo " value=\"".htmlspecialchars($_GET['pattern'])."\"" ?>/>
</tr><tr>
	<td align="right">Language:</td>
	<td><? printselect("lang",$lang,$_GET['lang']); ?></td>
</tr><tr>
	<td align="right">Wiki:</td>
	<td><? printselect("wiki",$wiki,$_GET['wiki']); ?></td>
</tr><tr>
	<td align="right">Namespace:</td>
	<td><? printselect("ns",$namespace,$_GET['ns']); ?></td>
</tr><tr>
	<td align="right">&nbsp;</td>
	<td><input type="checkbox" name="redirects"<? if(isset($_GET['redirects']) && $_GET['redirects']=='on') echo " checked"; ?>/> Include redirects</td>
</tr><tr>
	<td><input type="submit"/></td>
	<td><input type="reset"/></td>
</tr>
</table></form><?

if(isset($_GET['pattern']) &&
	isset($_GET['lang']) && isset($lang[$_GET['lang']]) &&
	isset($_GET['wiki']) && isset($wiki[$_GET['wiki']])) {
	mysql_connect($_GET['lang'].$_GET['wiki'].$config['rephostsufix'],$config['repuser'],$config['reppass']);
	mysql_select_db($_GET['lang'].$_GET['wiki']."_p");

	$ns=intval($_GET['ns']);
	if(!isset($namespace[$ns])) $ns=0;
	$ns_name=($ns==0?"":$namespace[$ns].":");

	if(!isset($_GET['redirects']) || $_GET['redirects']!='on') $redir=" AND page_is_redirect=0"; else $redir="";

	$res=mysql_query("SELECT page_title, page_is_redirect FROM page WHERE page_namespace=$ns $redir AND page_title REGEXP '".mysql_real_escape_string(strtr($_GET['pattern']," ","_"))."'");
	echo mysql_error();
	echo "<table border=\"1\">\n";
	while($r=mysql_fetch_assoc($res)) {
		echo "<tr><td><a href=\"http://$_GET[lang].$_GET[wiki].org/wiki/$ns_name$r[page_title]".($r['page_is_redirect']?"?redirect=no":"")."\">".strtr($r['page_title'],"_"," ")."</a></td></tr>\n";
	}
	echo "</table>\n";
}

foot();
?>
