<?

require 'bot.login.php';

//Get the list
$list = file_get_contents('http://toolserver.org/~nikola/grep.php?pattern=.pdf&lang=en&wiki=wikipedia&ns=6');
$list = explode('<td><input type="reset"/></td>',$list);
$list = explode('<table border="1">',$list[1]);

preg_match_all('/\wiki\/(Image\:.*?)\"\>/i', $list[1], $images);
$images = $images[1];
foreach ($images as $pdf)
{
sleep(60);
echo "Sleep 60\n";
        $text = $wiki->getpage("$pdf");
       if( preg_match("/\{\{(Bad(PDF|GIF| ?Format)|ShouldBe(Text|PNG|SVG)|Artifacts)\}\}/i",$text) || $wiki->nobots($pdf,$user,$text) == false)
        {
			echo "* - Skipping File:$pdf\n";
		}
		else
		{
                if(preg_match ("/== ?summary ?==/i",$text))
                {
                        echo "2 - Tagging $pdf\n";
                        //$pieces = explode("== Summary ==", $text);
						$pieces = preg_split("/== ?summary ?==/i",$text);
                        $above = $pieces[0];
                        $below = $pieces[1];
                        $newtext = "$above==Summary==\n{{BadFormat}} $below";
                        $edit_summary = "[[User:Addbot|Bot:]] Tagging PDF with {{BadFormat}}. [[User_Talk:Addbot|Report Errors]]";
                        $wiki->edit($pdf,$newtext,$edit_summary,true);
                }
                else
                {
						echo "1 - Tagging $pdf\n";
                        $newtext = "==Summary==\n{{BadFormat}}\n" .$text;
                        $edit_summary = "[[User:Addbot|Bot:]] Tagging PDF with {{BadFormat}}. [[User_Talk:Addbot|Report Errors]]";
                        $wiki->edit($pdf,$newtext,$edit_summary,true);
                }
        }     
}
//$wpi-&gt;forcepost('User:'.$user.'/Pdfbot/list',"Please add a list of pdf's here from [http://toolserver.org/~nikola/grep.php?pattern=%5C.pdf&lang=en&wiki=wikipedia&ns=6 here].",'Automatic list blanking (task complete).');

?>