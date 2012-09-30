<?php

/**
 * Tinychat Class File for PICKLES
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
 * Tinychat API Interface
 *
 * @link http://tinychat.com/developer/docs
 */
class API_Tinychat extends API_Common
{
	/**
	 * Public Key
	 *
	 * @access private
	 * @var    string
	 */
	private $public_key = null;

	/**
	 * Secret Key
	 *
	 * @access private
	 * @var    string
	 */
	private $secret_key = null;

	/**
	 * Constructor
	 *
	 * Assigns our public and secret keys from the configuration.
	 */
	public function __construct()
	{
		parent::__construct();

		if (isset($this->config->api['tinychat'], $this->config->api['tinychat']['public_key'], $this->config->api['tinychat']['secret_key']))
		{
			$this->public_key = $this->config->api['tinychat']['public_key'];
			$this->secret_key = $this->config->api['tinychat']['secret_key'];
		}
		else
		{
			throw new Exception('Unable to load TinyChat configuration.');
		}
	}

	/**
	 * Execute
	 *
	 * Constructs a valid API call, executes it and returns the results.
	 *
	 * @param string $codephrase name of the API call being called
	 * @param string $authentication post-codephrase portion of the auth string
	 * @param array $parameters key / value pairs for additional data
	 * @return array results of the API call
	 */
	private function execute($codephrase, $authentication, $parameters = null)
	{
		// Assembles and hashes the authentication token
		$authentication = md5($this->secret_key . ':' . $authentication);

		// Assembles any additional parameters
		$additional = '';

		if ($parameters && is_array($parameters))
		{
			foreach ($parameters as $key => $value)
			{
				$additional .= '&' . $key . '=' . $value;
			}
		}

		// Executes the API call
		$results = file_get_contents('http://tinychat.apigee.com/' . $codephrase . '?result=json&key=' . $this->public_key . '&auth=' . $authentication . $additional);

		return json_decode($results, true);
	}

	/**
	 * List Rooms
	 *
	 * Pulls all rooms for the API application.
	 *
	 * @return array API results
	 */
	public function listRooms()
	{
		return $this->execute('roomlist', 'roomlist');
	}

	/**
	 * Room Info
	 *
	 * Pulls the information for a room.
	 *
	 * @param string $room name of the room
	 * @param boolean $with_ip whether or not to include users IP addresses
	 * @return array API results
	 */
	public function roomInfo($room, $with_ip = false)
	{
		return $this->execute('roominfo', $room . ':roominfo', array('room' => $room, 'with_ip' => ($with_ip ? 1 : 0)));
	}

	/**
	 * Set Room Password
	 *
	 * Sets the password for the room, only users with the correct password
	 * will be able to enter.
	 *
	 * @param string $room name of the room
	 * @param string $password password to use, blank for no password
	 * @return array API results
	 */
	public function setRoomPassword($room, $password = '')
	{
		return $this->execute('setroompassword', $room . ':setroompassword', array('room' => $room, 'password' => $password));
	}

	/**
	 * Set Broadcast Password
	 *
	 * Sets the password to allow broadcasting in the room. Only users with the
	 * correct password will be able to broadcast.
	 *
	 * @param string $room name of the room
	 * @param string $password password to use, blank for no password
	 * @return array API results
	 */
	public function setBroadcastPassword($room, $password = '')
	{
		return $this->execute('setbroadcastpassword', $room . ':setbroadcastpassword', array('room' => $room, 'password' => $password));
	}

	/**
	 * Generate HTML
	 *
	 * Creates the HTML to place a chat on a site.
	 *
	 * @todo List params...
	 * @return array API results
	 */
	public function generateHTML($room, $join = false, $nick = false, $change = false, $login = false, $oper = false, $owner = false, $bcast = false, $api = false, $colorbk = false, $tcdisplay = false, $autoop = false, $urlsuper = false, $langdefault = false)
	{
		return '
			<script type="text/javascript">
				var tinychat = {'
					. 'room: "' . $room . '",'
					. ($join        ? 'join: "auto",'                        : '')
					. ($nick        ? 'nick: "' . $nick . '",'               : '')
					. ($change      ? 'change: "none",'                      : '')
					. ($login       ? 'login: "' . $login . '",'             : '')
					. ($oper        ? 'oper: "none",'                        : '')
					. ($owner       ? 'owner: "none",'                       : '')
					. ($bcast       ? 'bcast: "restrict",'                   : '')
					. ($api         ? 'api: "' . $api . '",'                 : '')
					. ($colorbk     ? 'colorbk: "' . $colorbk . '",'         : '')
					. ($tcdisplay   ? 'tcdisplay: "vidonly",'                : '')
					/* @todo Implement $autoop, it's an array and needs validated */
					. ($urlsuper    ? 'urlsuper: "' . $urlsuper . '",'       : '')
					. ($langdefault ? 'langdefault: "' . $langdefault . '",' : '')
					. 'key: "' . $this->public_key . '"'
				. '};
			</script>
			<script src="http://tinychat.com/js/embed.js"></script>
			<div id="client"></div>
		';
	}
}

?>
