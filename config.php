<?
global $config;

// main settings
$config['url'] = 'en.wikipedia.org';                        // wiki url we will be working on
$config['user'] = 'Addbot';                                 // bot username for login
$config['owner'] = 'Addshore';                              // bot owner
$config['sandbox'] = 'User:'.$config['user'].'/Sandbox';    // sandbox location
$config['debug'] = false;									// true for debugging
require '/home/addshore/.password.addbot';                  // $config['password'] = 'password';

// database settings
$config['dbhost'] = 'i-000000b4.pmtpa.wmflabs';
$config['dbport'] = '3306';
$config['dbuser'] = 'addshore';
require '/home/addshore/.password.db';            //$config['dbpass'] = 'password';
$config['dbname'] = 'addbot';

// table settings
$config['tblist'] = 'pending';                 // table containing articles to be checked
$config['tbdone'] = 'checked';                // table containing articles checked along with time

// tags
$config['tag']['wikify'] = new Template('Wikify',Array('Wfy','Wiki','Wkfy','Wikify section','Wf','Wikify-section','Wikifying'),true);
$config['tag']['emptysection'] = new Template('Emptysection',Array(''),true);
$config['tag']['uncategorizedstub'] = new Template('Uncategorized stub',Array('Uncat stub','Uncat-stub','Uncategorised stub','Uncategorisedstub','Uncategorizedstub','Uncatstub'),true);

$config['mitag']['Abbreviations'] = new Template('Abbreviations',Array('Abbrev'),true);
$config['mitag']['advert'] = new Template('advert',Array('Ad','Advertisement','Adcopy','No ads','Cleanup-advert','Cleanup-ad','Advertising','Cleanup-advertising','Advertisment','Spam-check','Cleanup spam','AD','Adspeak','Ad speak'),true);
$config['mitag']['autobiography'] = new Template('autobiography',Array('AUTO','Autobio'),true);
$config['mitag']['BLP IMDb-only refimprove'] = new Template('BLP IMDb-only refimprove',Array('BLP IMDb only','BLP IMDB only'),true);
$config['mitag']['BLP IMDb refimprove'] = new Template('BLP IMDb refimprove',Array('BLP IMDB sources','IMDB BLP refimprove','Refimprove IMDB BLP','BLP IMDB','BLP IMDB refimprove','BLPIMDBrefimprove','BLP IMDb'),true);
$config['mitag']['BLP sources'] = new Template('BLP sources',Array('Blpsources','BLPSources','BLPrefimprove','BLPsources','BLP Sources','Blp sources','BLPRefimprove','BLP Refimprove','BLP refimprove','Blprefimprove','Blp refimprove','BLP improvereferences','BLP Improvereferences','BLPimprovereferences','BLPImprovereferences','Blpimprovereferences','Blp improvereferences','Refimprove BLP','RefimproveBLP','Refimproveblp','BLP sourced','BLP moresources','ReferencedBLP','Referenced BLP','Refimprove blp','BLPmoreref','BLP improve','Refimprove-blp','Blp-refimprove','BLP source','BLP more sources'),true);
$config['mitag']['BLP unsourced'] = new Template('BLP unsourced',Array('UnsourcedBLP','BLPunreferenced','Unreferencedblp','Blpunsourced','BLPunsourced','Unsourcedblp','BLPUnreferenced','Unsourced BLP','BLP unreferenced','Blpunref','Unreferenced BLP','Blpunreferenced','UnreferencedBLP','BLPUnsourced','Unreferenced blp','BLP Unreferenced','Blp-unreferenced','Userspace BLP','Unreferenced-blp','Unreferenced-BLP','Blpnoref','Blp unreferenced','BLPnoref','Unref BLP','Blp unsourced','Urblp','Ublp','Blp-unsourced','BLPunref','Unsourced-blp','Noref-blp'),true);
$config['mitag']['citation style'] = new Template('citation style',Array('Cleanup-references','Cleanup-citation','Ref-cleanup','Citationstyle','Citation-style','Refstyle','Reference style','Reference-style','Cleanup-refs','Citestyle','Cleanrefs','Refclean','Refsclean','Source Style','Sourced wrong','Ref-style','Refcleanup','Citations style','Inconsistentfootnotes'),true);
$config['mitag']['cite check'] = new Template('cite check',Array('Cite-check','Citecheck'),true);
$config['mitag']['cleanup'] = new Template('cleanup',Array('Attention (on talk page)','Clean','Cu','CU','Tidy','Cleancat','Cleanup-quality','Cleanup-date','Attn','Attention see talk','Attention','Clean up','Cleanup-because','Clean-up','Cleanup-reason','Cleanup-since','Improve','Quality','Cleanup-article','Clu','Gamecleanup','Index-cleanup','Game cleanup'),true);
$config['mitag']['cleanup-laundry'] = new Template('cleanup-laundry',Array(''),true);
$config['mitag']['cleanup-link rot'] = new Template('cleanup-link rot',Array('Linkrot','Bare URLs','Link rot','Bare links','Barelinks','Bareurls','Bare urls','Cleanup-linkrot','Bare URL','BareURLs','Bare-URLs','Cleanup link-rot','Cleanup link rot','Bare refs','Bare references','Bareurl','BareURL','Bare','Cleanup-link-rot'),true);
$config['mitag']['cleanup-reorganize'] = new Template('cleanup-reorganize',Array('Cleanup-restructure','Bad structure','Organize','Cleanup restructure','CleanupRestructure','Restructure','Reorganize','Reorg','Structure','Cleanup-reorganise','Clean-up-reorganise','Organization','Reorganise','Cleanup reorganize','Disorganized','Cleanup-layout','Layout','Organize section'),true);
$config['mitag']['cleanup-rewrite'] = new Template('cleanup-rewrite',Array('Rewrite','Complete rewrite needed','Complete rewrite','Needs rewrite','Needsrewrite','Completerewrite','Pokerewrite','Cleanup rewrite','CleanupRewrite','Section rewrite','Sectionrewrite','Sect-rewrite','Awful'),true);
$config['mitag']['cleanup-spam'] = new Template('cleanup-spam',Array(''),true);
$config['mitag']['COI'] = new Template('COI',Array('Coi','Selfpromotion','COI-section','Conflict of interest','COI-check','COI-issues','Coi-section'),true);
$config['mitag']['colloquial'] = new Template('colloquial',Array(''),true);
$config['mitag']['confusing'] = new Template('confusing',Array('Cleanup-clarity','Simplify','Cleanup-confusing','Unclear','CleanupConfusing','Cleanupconfusing','Cleanup confusing','Badarticle','Unclear or confusing'),true);
$config['mitag']['context'] = new Template('context',Array('Cleanup-context','Bio-context','Insufficient context','Layman','Unnamed sport'),true);
$config['mitag']['contradict'] = new Template('contradict',Array('Contradictory','Contradiction','Contradicting','Conflicting','Contradict-self','Article contradicts itself'),true);
$config['mitag']['copy edit'] = new Template('copy edit',Array('Gcheck','Grammar check','Copy-edit','Cleanup-copyedit','Cleanup-english','Ortogramrevision','Grammar','Native speaker','Copyediting','NativeSpeaker','Copyedit','Spelling','Cleanup-grammer','Grammar-cleanup','Grammar cleanup','Cleanup grammar','Ced'),true);
$config['mitag']['criticism section'] = new Template('criticism section',Array('Controversy-section','Criticism','Csection','Criticism-section','Controversy section'),true);
$config['mitag']['crystal'] = new Template('crystal',Array('Speculative'),true);
$config['mitag']['Deadend'] = new Template('Deadend',Array(''),true);
$config['mitag']['disputed'] = new Template('disputed',Array('Dispute','Content disputed','BLP dispute','BLPdispute','Blpdispute','Accuracy','Factual accuracy disputed'),true);
$config['mitag']['essay-like'] = new Template('essay-like',Array('Essay-opinion','Essay-entry','Essaylike','Cleanup-essay','Cleanupessay','Reflection or essay','Essay like'),true);
$config['mitag']['example farm'] = new Template('example farm',Array('Examplefarm'),true);
$config['mitag']['expert'] = new Template('expert',Array(''),true);
$config['mitag']['external links'] = new Template('external links',Array('LinkFarm','Linkfarm','External-links','Cleanup External','External','Externallinks','Too many links','Excessive links','Cleanup-links','Toomanylinks','ExcessiveLinks','EL','Link farm','Extlinks','External linking'),true);
$config['mitag']['fanpov'] = new Template('fanpov',Array('Fansite'),true);
$config['mitag']['fiction'] = new Template('fiction',Array('Cleanup-fiction','Factfiction','Book-fiction'),true);
$config['mitag']['game guide'] = new Template('game guide',Array('Gameguide'),true);
$config['mitag']['globalize'] = new Template('globalize',Array('Limited Geographic Scope','LimitedGeographicScope','Limitedgeographicscope','Lgs','Limited geographic scope','World','Worldwide-view','Worldwideview','Worldwide','Globalise','Geographically limited','Worldview','Globalview','Global','Generic limited geographic scope','Worldwide view','Globalizestate','Worldwide View','World view'),true);
$config['mitag']['histinfo'] = new Template('histinfo',Array(''),true);
$config['mitag']['hoax'] = new Template('hoax',Array('Hx','Made-up','HOAX','Madeup'),true);
$config['mitag']['howto'] = new Template('howto',Array('Howto-section','Man-page','How to','How-to'),true);
$config['mitag']['inappropriate person'] = new Template('inappropriate person',Array('First-person','Incorrect person','Person','First person','Improper person'),true);
$config['mitag']['incomplete'] = new Template('incomplete',Array('Incomplete table'),true);
$config['mitag']['in-universe'] = new Template('in-universe',Array('Inuniverse','InUniverse','In universe','Universe'),true);
$config['mitag']['lead missing'] = new Template('lead missing',Array('Nointro','Nointroduction','No lead','Nolead','Missingintro','Intromissing','Opening','No-intro','Leadsection','Intro-missing','No lead section','Intro missing','Lede missing','No-lead','Missing lead','No intro','Lead absent','Lede absent','No lede','Noleadsection'),true);
$config['mitag']['lead rewrite'] = new Template('lead rewrite',Array('Vagueintro','LEDE','Introrewrite','Intro','Lead','Lede','Intro-rewrite','Lead-rewrite','Lede rewrite','No definition'),true);
$config['mitag']['lead too long'] = new Template('lead too long',Array('Introlength','Intro length','Cleanup-lead','Leadtoolong','LEAD','Longintro','Longlead','Longlede','Intro too long','Intro-toolong','Long lead','Intro-too long','Lede too long','Introtoolong','Intro-too-long'),true);
$config['mitag']['lead too short'] = new Template('lead too short',Array('Lead section','Expandlead','Too short','Leadtooshort','Tooshort','Short intro','Expand lead','Short lead','Intro-tooshort','Expandlede','Too Short','Lead-too-short','Shortlead','Shortintro','Lede too short','Intro too short','Build lead','Intro-expand'),true);
$config['mitag']['like resume'] = new Template('like resume',Array('Likeresume','Cleanup-resume','LikeResume','Like-resume','Cleanup resume','Resumelike','Resume like','Like a resume'),true);
$config['mitag']['more footnotes'] = new Template('more footnotes',Array('Somefootnotes','Morefootnotes','Moreinline','Morecite','More footnote'),true);
$config['mitag']['news release'] = new Template('news release',Array('Pressrelease','Press release','Newsrelease'),true);
$config['mitag']['no footnotes'] = new Template('no footnotes',Array('Inline citations','Indrefs','Citations','No citations','In-text citations','Nofootnote','Nocitations','Inline refs needed','Inline-citations','Inline','Nofootnotes','Needs footnotes','Nofn','No inline citations','Noinline','Inlinerefs','Inline-sources','In line citation','In-line citations'),true);
$config['mitag']['notability'] = new Template('notability',Array('NN','Explain-significance','Nonnotable','Explain significance','Explain-importance','Cleanup-significance','Significance','Explain-notability','Nn','Cleanup-notability','Notable','Episode-unreferenced','Episode notability','Episode-notability','Fiction notability','Notablity','Gng','Cleanup-importance','Non-notable','Non notable'),true);
$config['mitag']['one source'] = new Template('one source',Array('Singlesource','Single source','Oneref','Onesource','1source','Single-source'),true);
$config['mitag']['original research'] = new Template('original research',Array('OriginalResearch','Originalresearch','Original Research','Original-research','Original-Research','Or1','No original research'),true);
$config['mitag']['Orphan'] = new Template('Orphan',Array('Lonely','Orp','Do-attempt'),true);
$config['mitag']['out of date'] = new Template('out of date',Array('Outdated','Outofdate','Out-of-date'),true);
$config['mitag']['over-quotation'] = new Template('over-quotation',Array('Quotefarm','Quote farm','Longquote','Overquotation','Too many quotes'),true);
$config['mitag']['overly detailed'] = new Template('overly detailed',Array('Tribute','Fancruft','Overdetailed','Too detailed','Over-detailed','Over detailed','Excessive detail','Overly','Too much detail'),true);
$config['mitag']['peacock'] = new Template('peacock',Array('Wikipuffery'),true);
$config['mitag']['plot'] = new Template('plot',Array('Long plot summary','PLOT','Long-plot','Long plot','Longplot','Too much plot'),true);
$config['mitag']['POV'] = new Template('POV',Array('Npov','Pov','Bias','NPOV','Neutrality','Point Of View','PoV','Neutral','Biased','NPOV-dispute','POV dispute','Too friendly','White washed','Pov problem'),true);
$config['mitag']['POV-check'] = new Template('POV-check',Array('Pov check','POV check','POV-check-section','POV Check','NPOV Check','NPOV check','Pov-Check','POV-Check','Pov-check','Povcheck','POVCheck','POVcheck','Pov Check','Pov-check-section'),true);
$config['mitag']['primary sources'] = new Template('primary sources',Array('PrimarySources','Primary Sources','Primarysource','Primarysources','Primary','Primary source','3rdpartysources','3rdparty'),true);
$config['mitag']['prose'] = new Template('prose',Array('Not list','List to prose','Prose timeline','Proseline','Prosetimeline','ProseTimeline','List'),true);
$config['mitag']['recentism'] = new Template('recentism',Array('Recent'),true);
$config['mitag']['refimprove'] = new Template('refimprove',Array('Verify','Not verified','Cleanup-verify','Notverified','Cite sources','Sources','More sources','Citations missing','Referenced','Citations needed','Moresources','Ri','Missing citations','Morerefs','Morereferences','Moreref','Fewreferences','Cleanup cite','Few references','More references','Improve-refs','Improve-references','Ref-improve','Reference-improve','Ref improve','Improve references','Improvereferences','Improverefs','Improve refs','RefImprove','Verification','Additionalcitations','Additional citations','Improveref','Fewrefs','Few refs','Reimprove','More refs','Ref Improve','Reference improve','Refimproved','Needs more references','Ref-Improve','Citationsneeded','Referencing','Citations improve','Refimporve','Refim','Badrefs','Unreliable references','Needs additional citations','Add references'),true);
$config['mitag']['review'] = new Template('review',Array('Review-section'),true);
$config['mitag']['Sections'] = new Template('Sections',Array('Needsections','Cleanupsections','Needs sections'),true);
$config['mitag']['self-published'] = new Template('self-published',Array('Secondary','Selfpublished','Self published'),true);
$config['mitag']['story'] = new Template('story',Array(''),true);
$config['mitag']['synthesis'] = new Template('synthesis',Array('Previously unpublished synthesis','Unpublished synthesis'),true);
$config['mitag']['technical'] = new Template('technical',Array('Cleanup-technical','Technical edit','Tech','Cleanup-cliche','Cleanup-jargon','Cleanup-cliché','Tootechnical','Cleanup-Jargon','Jargon cleanup','Cleanup jargon','Toospecialized','Too specialized','Technobabble','Technical2','Too much jargon','Tech jargon','Technical-article','Jargon','Complex','Jargon-section','Too technical'),true);
$config['mitag']['tone'] = new Template('tone',Array('Cleanup-tone','Unencyclopedic tone','Unencyclopaedic tone','Magazine','Formal tone','Formal','Inappropriatetone','Cleanup tone','Unencyclopædic-tone','Unencyclopædic tone','Informal','Inappropriate tone'),true);
$config['mitag']['travel guide'] = new Template('travel guide',Array('Travelguide'),true);
$config['mitag']['trivia'] = new Template('trivia',Array('Too much trivia','Cleanup-trivia','Toomuchtrivia','Trivia section','Miscellanea'),true);
$config['mitag']['unbalanced'] = new Template('unbalanced',Array('Balance','Topheavy','Limited'),true);
$config['mitag']['Unreferenced'] = new Template('Unreferenced',Array('Unsourced','Unverified','Unref','References','Uncited-article','Citesources','NR','No references','Unrefarticle','Unreferenced article','Noref','Norefs','Noreferences','Cleanup-cite','References needed','Nr','No refs','UnreferencedArticle','No ref','Unreferenced stub','Needs references','Noreference','No reference','Refsneeded','Refs needed','Ref needed','Nosources','No sources','UNref'),true);
$config['mitag']['Uncategorized'] = new Template('Uncategorized',Array('CatNeeded','Catneeded','Uncategorised','Uncat','Categorize','Categories needed','Categoryneeded','Category needed','Category requested','Categories requested','Nocats','Categorise','Nocat','Needs cat','Needs cats','Cat needed','Cats needed','Nocategory','No category','No categories'),true,Array($config['tag']['uncategorizedstub']));
$config['mitag']['unreliable sources'] = new Template('unreliable sources',Array('Unreliable','Reliablesources','Reliable sources','Unreliablesources','Reliable-sources'),true);
$config['mitag']['update'] = new Template('update',Array('Not up to date','Notuptodate','UpdateWatch','Update sect'),true);
$config['mitag']['very long'] = new Template('very long',Array('Long','Toobig','Longish','VeryLong','Shorten','Too long','Toolong','Too-long','Tldr','TLDR','Verylong','Looong','Loong'),true);
$config['mitag']['Weasel'] = new Template('Weasel',Array('Weasel words','Weasel-words','Weasel section','Weaselwords'),true);
$config['mitag']['Bad format'] = new Template('Bad format',Array('BadPDF','Badpdf','Badformat','BadFormat'),true);

?>
