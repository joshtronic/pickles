<?php

/**
 * Browser Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Browser Utility Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant browser-related tasks.
 */
class Browser
{
	/**
	 * Redirect
	 *
	 * Redirects the browser to another URL. Stops execution as to not run code
	 * erroneously due to output buffering. HTTP/1.1 request an absolute URI,
	 * hence the inclusion of the scheme, hostname and absolute path if :// is
	 * not found. Don't hate the player, hate the RFC.
	 *
	 * @static
	 * @param  string $destination URL to redirect to
	 * @link   http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.30
	 */
	public static function redirect($destination)
	{
		if (strpos($destination, '://') === false)
		{
			$destination = 'http' . ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . $destination;
		}

		header('Location: ' . $destination);
		exit;
	}

	/**
	 * Status
	 *
	 * Pushes a status code to the browser. Some of these codes are site (420)
	 * and server (444) specific, some just for LOLs (418) and some that are
	 * still in draft (425) and subject to change. I wanted this to be a
	 * complete list and in the scenario that a code had multiple meanings, I
	 * favored a more recent RFC (424) even if merely a draft (451).
	 *
	 * @static
	 * @param  integer status response code
	 */
	public static function status($code = 200)
	{
		switch ($code)
		{
			// {{{ 1xx Informational
			case 100: $message = '100 Continue';                             break;
			case 101: $message = '101 Switching Protocols';                  break;
			case 102: $message = '102 Processing';                           break;
			// }}}
			// {{{ 2xx Success
			case 200: $message = '200 OK';                                   break;
			case 201: $message = '201 Created';                              break;
			case 202: $message = '202 Accepted';                             break;
			case 203: $message = '203 Non-Authoritative Information';        break;
			case 204: $message = '204 No Content';                           break;
			case 205: $message = '205 Reset Content';                        break;
			case 206: $message = '206 Partial Content';                      break;
			case 207: $message = '207 Multi-Status';                         break;
			case 208: $message = '208 Already Reported';                     break;
			case 226: $message = '226 IM Used';                              break;
			// }}}
			// {{{ 3xx Redirection
			case 300: $message = '300 Multiple Choices';                     break;
			case 301: $message = '301 Moved Permanently';                    break;
			case 302: $message = '302 Found';                                break;
			case 303: $message = '303 See Other';                            break;
			case 304: $message = '304 Not Modified';                         break;
			case 305: $message = '305 Use Proxy';                            break;
			case 306: $message = '306 Switch Proxy';                         break;
			case 307: $message = '307 Temporary Redirect';                   break;
			case 308: $message = '308 Permanent Redirect';                   break;
			// }}}
			// {{{ 4xx Client Error
			case 400: $message = '400 Bad Request';                          break;
			case 401: $message = '401 Unauthorized';                         break;
			case 402: $message = '402 Payment Required';                     break;
			case 403: $message = '403 Forbidden';                            break;
			case 404: $message = '404 Not Found';                            break;
			case 405: $message = '405 Method Not Allowed';                   break;
			case 406: $message = '406 Not Acceptable';                       break;
			case 407: $message = '407 Proxy Authentication Required';        break;
			case 408: $message = '408 Request Timeout';                      break;
			case 409: $message = '409 Conflict';                             break;
			case 410: $message = '410 Gone';                                 break;
			case 411: $message = '411 Length Required';                      break;
			case 412: $message = '412 Precondition Failed';                  break;
			case 413: $message = '413 Request Entity Too Large';             break;
			case 414: $message = '414 Request-URI Too Long';                 break;
			case 415: $message = '415 Unsupported Media Type';               break;
			case 416: $message = '416 Requested Range Not Satisfied';        break;
			case 417: $message = '417 Expectation Failed';                   break;
			case 418: $message = '418 I\'m a teapot';                        break;
			case 420: $message = '420 Enhance Your Calm';                    break;
			case 422: $message = '422 Unprocessed Entity';                   break;
			case 423: $message = '423 Locked';                               break;
			case 424: $message = '424 Failed Dependency';                    break;
			case 425: $message = '425 Unordered Collection';                 break;
			case 426: $message = '426 Upgrade Required';                     break;
			case 428: $message = '428 Precondition Required';                break;
			case 429: $message = '429 Too Many Requests';                    break;
			case 431: $message = '431 Request Header Fields Too Large';      break;
			case 444: $message = '444 No Response';                          break;
			case 449: $message = '449 Retry With';                           break;
			case 450: $message = '450 Blocked by Windows Parental Controls'; break;
			case 451: $message = '451 Unavailable for Legal Reasons';        break;
			case 494: $message = '494 Request Header Too Large';             break;
			case 495: $message = '495 Cert Error';                           break;
			case 496: $message = '496 No Cert';                              break;
			case 497: $message = '497 HTTP to HTTPS';                        break;
			case 499: $message = '499 Client Closed Request';                break;
			// }}}
			// {{{ 5xx Server Error
			case 500: $message = '500 Internal Server Error';                break;
			case 501: $message = '501 Not Implemented';                      break;
			case 502: $message = '502 Bad Gateway';                          break;
			case 503: $message = '503 Service Unavailable';                  break;
			case 504: $message = '504 Gateway Timeout';                      break;
			case 505: $message = '505 HTTP Version Not Supported';           break;
			case 506: $message = '506 Variant Also Negotiates';              break;
			case 507: $message = '507 Insufficient Storage';                 break;
			case 508: $message = '508 Loop Detected';                        break;
			case 509: $message = '509 Bandwidth Limit Exceeded';             break;
			case 510: $message = '510 Not Extended';                         break;
			case 511: $message = '511 Network Authentication Required';      break;
			case 598: $message = '598 Network read timeout error';           break;
			case 599: $message = '599 Network connect timeout error';        break;
			// }}}
			default:  $message = '200 OK';                                   break;
		}

		header('HTTP/1.1 ' . $message, true, $code);
		header('Status: '  . $message, true, $code);
	}
}

?>
