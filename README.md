# PHP Interface Collection of Killer Libraries to Enhance Stuff

## What is PICKLES?

PICKLES is an open source framework for the rapid development of web applications.

## Okay, but why?

I could have went with any number of existing frameworks, but I opted to build my own because I like to code. I've built quite a few sites from the ground up over the years, and I wanted to roll that experience into a single system that I could not only use for my sites, but share with the world.

## Wait, what, it's not MVC?

PICKLES is in fact not a true MVC system and won't be masquerading around as one (yeah, I know, I borrowed some naming conventions). PICKLES does have a main controller that handles incoming page views. The controller loads a module that contains all of the business logic (optionally interacting with data models) and then execution is passed off to the display layer. The display layer gives the user what they asked for (even if they didn't say please). This is how web pages work, and there has never been a reason for me to force PICKLES into the MVC box just for the hell of it.

## Requirements

### Required Software

* Apache (should run on 1.3+)
* Apache Module mod_rewrite
* PHP 5.0+

### Highly Recommended Software

* PHP 5.2.0+ for native JSON support or PECL JSON 1.2.1

### Optional Software

* node, npm & lessc to compile LESS files
* sass to compile SASS files
* MySQL with PDO and PDO_MYSQL drivers
* PostgreSQL with PDO and PDO_PGSQL drivers
* SQLite 3 with PDO and PDO_SQLITE drivers

## Installation

Installation is quite simple as there is no installer to run, and all server configuration options can be set in your index.php for your site.

1. Download the source [[http://github.com/joshtronic/pickles/zipball/master]] (or clone the repository)

2. Place the code anywhere you'd like (that's at least 2 directories up from the root of your website). I recommend using /usr/share/pickles

3. A starter site can be obtained from [[http://github.com/joshtronic/pickles-starter]]. It has everything you need to get a site up and running.

4. At this point you should have a very rudimentary site up and running.
