Maximizing Resources for Student Success
=======================

Introduction
------------
This application is a project of the National Higher Education Benchmark Institute
 at Johnson County Community College, with help from the Bill and Melinda Gates
 Foundation. Its purpose is to collect data from community colleges around the
 country and present reports and analysis to colleges and policy makers.

Requirements
------------
Requires PHP 5.4 and Git. Other dependencies are installed with Composer (see
below).

Installation
------------
Install from GitHub with this command:

     git clone git@github.com:nhebi/mrss.git

Then install dependencies using Composer:
    
     php composer.phar install

That places the dependency binaries in the __bin__ directory. For convenience, add that directory to your path:

     export PATH=./bin:$PATH

Building and Testing
------------
MRSS uses the build tool [Phing] (http://www.phing.info/). To do a test build, invoke it like this:

     phing
     
That one command should put the app through its paces like this (as defined by build.xml):

+ Check coding standard compliance (PSR2) with [PHP_CodeSniffer] (http://pear.php.net/package/PHP_CodeSniffer)
+ Create a test database (Assumes you have a mysql user named _root_ with password _root_ that has permission to create a database.)
+ Run db migrations to bring the db structure up to date (uses Doctrine migrations)
+ Start the built-in web server
+ Run [Behat] (http://behat.org/) scenarios (behavior driven development)
+ Restore original db config
+ Run unit tests with [PHP Unit] (https://github.com/sebastianbergmann/phpunit/)

If the build fails for any reason, you'll see red text. If it succeeds,
you'll see: __BUILD PASSED__.

Test
