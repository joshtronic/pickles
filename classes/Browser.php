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
 * @copyright Copyright 2007-2013, Josh Sherman
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
class Browser extends Object
{
	/**
	 * Attributes
	 *
	 * Variables passed on the query string as /var:value/
	 *
	 * @access private
	 * @var    array
	 */
	private $attributes = array();

	/**
	 * Get instance of the object
	 *
	 * Let's the parent class do all the work
	 *
	 * @static
	 * @param  string $class name of the class to instantiate
	 * @return object self::$instance instance of the Config class
	 */
	public static function getInstance($class = 'Browser')
	{
		return parent::getInstance($class);
	}

	/**
	 * Set browser variable
	 *
	 * Sets a variable in the attributes array for easier access later.
	 *
	 * @static
	 * @param  string  $variable name of the variable to set
	 * @param  mixed   $value the value to set to the variable
	 * @return boolean true
	 */
	public static function set($variable, $value)
	{
		$browser                        = Browser::getInstance();
		$browser->attributes[$variable] = $value;

		return true;
	}

	/**
	 * Get browser variable
	 *
	 * Gets a variable passed in from the browser. Currently only supports
	 * the custom attribute URI format /$variable:$value/.
	 *
	 * @static
	 * @param  string $variable name of the variable to get
	 * @return mixed the value of the variable or boolean false if not set
	 */
	public static function get($variable)
	{
		$browser = Browser::getInstance();

		if (isset($browser->attributes[$variable]))
		{
			return $browser->attributes[$variable];
		}

		return false;
	}

	/**
	 * Is Mobile
	 *
	 * Detects if we're working with a mobile browser.
	 *
	 * @return boolean whether or not the browser is considered mobile
	 */
	public static function isMobile()
	{
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

		return preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $user_agent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($user_agent, 0, 4)) || strpos('iPhone', $user_agent);
	}

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
			$destination = 'http' . ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' || $_SERVER['HTTPS'] == '') ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . $destination;
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
