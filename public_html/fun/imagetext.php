<?php

//Set up our words

$x['hello'][0]='Movicons2-hello.gif';
$x['yes'][0]='P yes green.svg';
$x['yes'][1]='Yes check.gif';
$x['no'] = array('P no red.svg','No sign.svg');
$x['one'][0]='Pictogram voting number one.svg';
$x['dead'][0]='Dead.png';
$x['copyright'][0]='Copyright maybe.svg';
$x['dog'][0]='Icon dog.gif';
$x['mop'][0]='Mop.svg';
$x['wikidata'][0]='Wikidata-logo.svg';
$x['wikipedia'][0]='Wikipedia-logo.png';
$x['book'][0]='Office-book.svg';
$x['star'][0]='Cscr-featured.svg';
$x['infomation'][0]='Gtk-dialog-info.svg';
$x['square'][0]='Square.gif';
$x['red'][0]='Red-square.gif';
$x['circle'][0]='Circle.png';
$x['and'] = array('OCR-A char Ampersand.svg','Glossy 3d blue ampersand.png','U+214B.svg','Plus sign.svg','Plus sign font awesome.svg','Plus in circle.svg');
$x['101'][0]='Elongated circle 101.svg';
$c['colorfull'][0] = 'Circle diagram1.png';

$s = 'x25px';

//Now for the actual script

$text = $_GET['text'];
$words = explode(' ',$text);
$return = "";
foreach ($words as $word)
{
	if(isset($x[$word]))
	{
		$word = "[[File:".$x[$word][rand(0,count($x[$word])-1)]."|$s]]";
	}
	$return .= $word.' ';
}


//Return our text with the image replacments
echo trim($return);

?>