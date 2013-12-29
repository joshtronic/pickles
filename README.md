# PHP Interface Collection of Killer Libraries to Enhance Stuff

[![Build Status](https://travis-ci.org/joshtronic/pickles.png)](https://travis-ci.org/joshtronic/pickles)

## What is PICKLES?

PICKLES is an open source framework for the rapid development of web applications.

## Okay, but why?

I could have went with any number of existing frameworks, but I opted to build my own because I like to code. I’ve built quite a few sites from the ground up over the years, and I wanted to roll that experience into a single system that I could not only use for my sites, but share with the world.

## Wait, what, it’s not MVC?

PICKLES is in fact not a true MVC system and won’t be masquerading around as one (yeah, I know, I borrowed some naming conventions). PICKLES does have a main controller that handles incoming page views. The controller loads a module that contains all of the business logic (optionally interacting with data models) and then execution is passed off to the display layer. The display layer gives the user what they asked for (even if they didn’t say please). This is how web pages work, and there has never been a reason for me to force PICKLES into the MVC box just for the hell of it.

## Requirements

### Required Software

* Web server of your choice (nginx is highly recommended)
* PHP 5.4+

Please note that strides are being made to be compatible with bleeding edge technologies. PICKLES is currently developed against PHP 5.5 with builds still passing against PHP 5.4. This effort will be with somewhat of a reckless abandon towards backwards compatibility to keep up with deprecations within PHP.

To help anyone that is “stuck” using PHP 5.3.x, the version distributed with Ubuntu 12.04 LTS, you can still use the 5.3 compatible branch [available here](https://github.com/joshtronic/pickles/archive/php53-compatible.zip) instead. This branch is a fork of the master branch just before we started to these efforts and is considered very stable. The branch will remain available for the foreseeable future but will be indefinitely frozen unless pull requests are submitted.

PHP 5.4 will only be supported until it stops passing builds and will subsequently be branched in the same manner as the PHP 5.3 compatibile branch.

As developers we make demands that our end users use modern day browsers while we’re just as guilty by running older server software. I feel that we should be holdings ourselves to the same standards when it comes to our server stacks. Stability is great, but at a certain point you’re sacrificing your own advancements as a developer as well as turning a blind eye to optimizations that can benefit your users.

### Optional Software

#### Datastores

* MySQL with PDO and PDO_MYSQL drivers
* PostgreSQL with PDO and PDO_PGSQL drivers
* SQLite 3 with PDO and PDO_SQLITE drivers
* Memcached with the Memcache module

#### CSS Pre-processors

* node, npm & lessc to compile LESS files
* sass to compile SASS files

## Installation

Installation is quite simple as there is no installer to run, and all server configuration options can be set in your index.php for your site.

1. [Download the PICKLES source code](https://github.com/joshtronic/pickles/archive/master.zip) (or clone the repository)
2. Place the code anywhere you’d like (that’s at least 2 directories up from the root of your website). I recommend using `/usr/share/pickles`
3. A starter site can be obtained from http://github.com/joshtronic/pickles-starter. It has everything you need to get a site up and running.
4. At this point you should have a very rudimentary site up and running.
