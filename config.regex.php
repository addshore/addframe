<?

// regex to match the date used in maintanace templates
$date = "((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])"

// regex to match each maintanence template
// uncat
$tag['uncat']['templates'] = array(
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
$tag['uncat']['regex'] = "(".implode('|',$tag['uncat']).")";
// orphan
$tag['orphan']['templates'] = array(
	'Orphan',
	'Do-attempt',
	'Lonely',
	'Orp',
);
$tag['orphan']['regex'] = "(".implode('|',$tag['orphan']).")";
// deadend
$tag['deadend']['templates'] = array(
	'Deadend',
	'Dep',
	'Dead end page',
	'Dead-end',
	'Needs links',
);
$tag['deadend']['regex'] = "(".implode('|',$tag['deadend']).")";
// sections
$tag['sections']['templates'] = array(
	'Sections',
	'Needsections',
	'Cleanupsections',
	'Needs sections',
);
$tag['sections']['regex'] = "(".implode('|',$tag['sections']).")";
// unref
$tag['unref']['templates'] = array(
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
$tag['unref']['regex'] = "(".implode('|',$tag['unref']).")";
// emptysection
$tag['emptysection']['templates'] = array(
	'Emptysection',
	'Empty-section',
	'EmptySection',
	'Emptysect',
	'No content',
);
$tag['emptysection']['regex'] = "(".implode('|',$tag['emptysection']).")";

$tag['badformat'] = "";
$tag['stub'] = "";
?>