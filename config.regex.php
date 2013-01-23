<?

// regex to match the date used in maintanace templates
$config['date'] = "((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])";

// regex to match each maintanence template
// uncat
$config['tag']['uncat']['templates'] = array(
	'Uncategorized',
	'Cat needed',
	'CatNeeded',
	'Categories needed',
	'Categories requested',
	'Categorise',
	'Categorize',
	'Category needed',
	'Category requested',
	'Categoryneeded',
	'Cats needed',
	'Needs Cat',
	'Needs Cats',
	'No categories',
	'No category',
	'Nocat',
	'Nocategory',
	'Nocats',
	'Uncat',
	'Uncategorised',
);
$config['tag']['uncat']['regex'] = "(".implode('|',$config['tag']['uncat']['templates']).")";
// orphan
$config['tag']['orphan']['templates'] = array(
	'Orphan',
	'Do-attempt',
	'Lonely',
	'Orp',
);
$config['tag']['orphan']['parameters'] = array(
	'att',
	'geo',
	'few',
	'incat',
);
$config['tag']['orphan']['regex'] = "(".implode('|',$config['tag']['orphan']['templates']).")(\|(".implode('|',$config['tag']['orphan']['parameters']).") ?= ?[0-9a-z _]){0,".count($config['tag']['orphan']['parameters'])."}";
// deadend
$config['tag']['deadend']['templates'] = array(
	'Deadend',
	'Dep',
	'Dead end page',
	'Dead-end',
	'Needs links',
);
$config['tag']['deadend']['regex'] = "(".implode('|',$config['tag']['deadend']['templates']).")";
// sections
$config['tag']['sections']['templates'] = array(
	'Sections',
	'Needsections',
	'Cleanupsections',
	'Needs sections',
);
$config['tag']['sections']['regex'] = "(".implode('|',$config['tag']['sections']['templates']).")";
// unref
$config['tag']['unref']['templates'] = array(
	'Unreferenced',
	'Citesources',
	'Cleanup-cite',
	'NR',
	'Needs references',
	'No ref',
	'No reference',
	'No references',
	'No refs',
	'No sources',
	'Noref',
	'Noreference',
	'Noreferences',
	'Norefs',
	'Nosources',
	'Nr',
	'Ref needed',
	'References',
	'References-needed',
	'Refs needed',
	'Refsneeded',
	'UNref',
	'Uncited-article',
	'Unref',
	'Unrefarticle',
	'Unreferences article',
	'Unreferenced stub',
	'UnreferencedArticle',
	'Unsourced',
	'Unverified',
);
$config['tag']['unref']['regex'] = "(".implode('|',$config['tag']['unref']['templates']).")";
// emptysection
$config['tag']['emptysection']['templates'] = array(
	'Emptysection',
	'Empty-section',
	'EmptySection',
	'Emptysect',
	'No content',
);
$config['tag']['emptysection']['regex'] = "(".implode('|',$config['tag']['emptysection']['templates']).")";

//TODO: Add badformat and stub t
$config['tag']['badformat'] = "";
$config['tag']['stub'] = "";
?>