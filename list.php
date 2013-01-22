<?

//login to api

//CASE SELECT $type

//CATEGORY
	//presume $recursive is true (if not set)
	//get all members of the category

//PAGE
	//get the page
	//parse the page (this could be with or without wikilinks) pages broken with linbreaks

//TEMPLATE
	//get transclusions of the template

//HTML
	//if $trigger is set get the trigger page and wait for 30 secs
	//get the main page (this could be with or without wikilinks) pages broken with linbreaks


//After the list has been generated
	//remove duplicates
	//restrict the namespace depending on $ns (presume article namespace only if not set)
	
//NOW either connect to db and load all of the pages into the table (making sure we dont add duplicates)
//OR write to a simple text file (appending to the list currently (making sure we dont add duplicates)

//write to logfile saying the list was generated log.txt?
?>