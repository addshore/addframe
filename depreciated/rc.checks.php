<?PHP
/*
$change['received'] = microtime(1);
$change['namespace'] = $m[1];
$change['title'] = $m[5];
$change['flags'] = $m[6];
$change['url'] = $m[7];
$change['id1'] = $m[9]; //Normal=diff Newpage=oldid
$change['id2'] = $m[11]; //Normal=oldid Newpage=rcid
$change['user'] = $m[12];
$change['length'] = $m[14];
$change['comment'] = $m[15];
$change['name'] = $m[1].$m[5];
*/

if ( preg_match("/^Wikipedia:Tutorial \((Editing|Formatting|Wikipedia links|Citing sources|Keep in mind)\)$/",$change['name']) 
|| preg_match("/^Wikipedia talk:Tutorial\/(Editing|Formatting|Wikipedia links|Citing sources|Keep in mind)\/sandbox$/",$change['name']) 
|| preg_match("/^Wikipedia( talk)?:Sandbox$/",$change['name']) 
|| preg_match("/^Template( talk)?:X[1-9]$/",$change['name']) 
&& $change['user'] != $user)
{
	
	if($change['name'] == "Wikipedia:Sandbox")
	{$header = $wiki->getpage($change['name'],"531626860");}
	if($change['name'] == "Wikipedia talk:Sandbox")
	{$header = $wiki->getpage($change['name'],"531474752");}
	if($change['name'] == "Template:Template sandbox")
	{$header = $wiki->getpage($change['name'],"531539988");}
	if(preg_match("/Wikipedia:Tutorial \((Editing|Formatting|Wikipedia links|Citing sources|Keep in mind)\)/",$change['name']))
	{$header = $wiki->getpage("Wikipedia:Tutorial/Editing/sandbox","531634619");}
	if(preg_match("/Template:X[1-9]/",$change['name']))
	{$header = $wiki->getpage("Template:X1","529773572");}
	if(preg_match("/Template talk:X[1-9]/",$change['name']) || preg_match("/Wikipedia talk:Tutorial\/(Editing|Formatting|Wikipedia links|Citing sources|Keep in mind)\/sandbox/",$change['name']) )
	{$header = $wiki->getpage("Template talk:X1","525055639");}

	if (strpos($wiki->getpage($change['name'],null,true),$header) === FALSE) {
		sleep(10);
		$wiki->edit($change['name'],$header,"[[User:Addbot|Bot:]] Restoring ".$change['name']." header",true,true,null,true);
	}
	unset($header);
}

?>
