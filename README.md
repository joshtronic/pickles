[![Stories in Ready](https://badge.waffle.io/joshtronic/pickles.png?label=ready&title=Ready)](https://waffle.io/joshtronic/pickles)
# PICKLES [![Build Status](https://travis-ci.org/joshtronic/pickles.png?branch=master)](https://travis-ci.org/joshtronic/pickles) [![Coverage Status](https://coveralls.io/repos/joshtronic/pickles/badge.png)](https://coveralls.io/r/joshtronic/pickles) [![Dependency Status](https://www.versioneye.com/user/projects/52d1bc1eec13751bde00002a/badge.png)](https://www.versioneye.com/user/projects/52d1bc1eec13751bde00002a)

PICKLES (PHP Interface Collection of Killer Libraries to Enhance Stuff) is an
open source framework for rapid PHP development. PICKLES aims to be an “API
First” system for building APIs as well as AJAX/AJAJ-centric web applications.

## Requirements

### Required Software

* Web server of your choice (nginx is highly recommended but Apache with
  `mod_rewrite` will suffice)
* PHP 5.4+

Please note that PICKLES development is focused on the most recent stable
version of PHP (currently 5.5) but will maintain backwards compatibility with
the previous stable version. It may not be immediate, but when PHP 5.6 is
released compatibility for PHP 5.4 will be dropped in favor of modern
niceties.

For anyone stuck using PHP 5.3 is welcome to use [PICKLES v13.12.x][v13.12]
which at this time is still receiving bug fixes but will not be seeing any new
development by myself. Pull requests are welcome.

My rant about outdated server stacks can be found [on my blog][StackPost]

### Optional Software

* MySQL server with the `PDO_MYSQL` driver
* PostgreSQL server with the `PDO_PGSQL` driver
* SQLite 3 with the `PDO_SQLITE` driver
* Memcached server with the `Memcache` module
* `composer` if you want to compile LESS, SCSS or JS also necessary if you
  want to use AYAH integration or run the test suite
* [UOPZ][UPOZ] if you want to be able to run the test suite

## Installation

Installation is quite simple as there is no installer to run, and all server
configuration options can be set in your index.php for your site.

1. [Download the PICKLES source][MasterZip] (or clone the repository)
2. Place the code anywhere you’d like (that’s at least 2 directories up from
   the root of your website). I recommend using `/usr/share/pickles[-vVERSION]`
3. Run `composer update`
4. A site already built in PICKLES can be found [here][HolidayAPI]

## TODO

* Bring the project's Wiki up to date
* Build an actual boilerplate site that would be included in this project

## Thanks

Special thanks to [Geoff Oliver][GeoffOliver] for being a long time user and
contributor of PICKLES and to [Dean Jones][DeanJones] for coming up with the
PICKLES acronym.

  [DeanJones]:   https://github.com/deanproxy
  [GeoffOliver]: https://github.com/geoffoliver
  [HolidayAPI]:  https://github.com/gravityblvd/tools.gravityblvd.com
  [MasterZip]:   https://github.com/joshtronic/pickles/archive/master.zip
  [StackPost]:   http://joshtronic.com/2014/01/13/your-stack-is-outdated/#.UuVzI3n0A18
  [UPOZ]:        https://github.com/krakjoe/uopz
  [v13.12]:      https://github.com/joshtronic/pickles/tree/13.12
