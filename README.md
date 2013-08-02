Addwiki MediaWiki Framework
=======

This is the Addframe framework for Mediawiki sites
The framework is currently under development (feel free to hack along)!

If you have feature requests please file a bug

How to use
-------------

Take a look at some example scripts in scripts/HelloWorld to see basic use.

```php
use Addframe\Family;
use Addframe\Globals;
use Addframe\UserLogin;
require_once( dirname( __FILE__ ) . '/../../init.php' );

$wm = new Family(
	new UserLogin( Globals::$config['wikiuser']['username'],
		Globals::$config['wikiuser']['password'] ), Globals::$config['wikiuser']['home'] );
$enwiki = $wm->getSite( 'en.wikipedia.org' );
$sandbox = $enwiki->newPageFromTitle( 'Wikipedia:Sandbox' );
$sandbox->wikiText->appendText( "\nThis is a simple edit to this page!" );
$sandbox->save( 'This is a simply summary');
```


Directory Structure
-------------

* configs - For user specific config files
* includes - All framework classes are in here (split into subdirectories)
* scripts - Scripts that use the framework
* tests - Tests for the framework

Tests
-------------

The framework is tested using PHPUnit tests. (see /tests)
The bootstrap file for the tests can be found at /tests/bootstrap.php
The configuration file for the tests can be found at /tests/phpunit.xml
On any push, branch or pull request Jenkins will run all tests
If Jenkins reports failing tests please try to fix them ASAP
When writing new code please add tests for the code!