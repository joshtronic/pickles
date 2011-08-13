<?php

class API_Tinychat extends API_Common
{
	private $public_key = null;
	private $secret_key = null;

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

	private function execute($codephrase, $authentication, $parameters = null)
	{
		$authentication = md5($this->secret_key . ':' . $authentication);
		
		$additional = '';

		if ($parameters && is_array($parameters))
		{
			foreach ($parameters as $key => $value)
			{
				$additional .= '&' . $key . '=' . $value;
			}
		}

		$results = file_get_contents('http://tinychat.apigee.com/' . $codephrase . '?result=json&key=' . $this->public_key . '&auth=' . $authentication . $additional);

		return json_decode($results, true);
	}

	public function listRooms()
	{
		return $this->execute('roomlist', 'roomlist');
	}

	public function roomInfo($room, $with_ip = false)
	{
		return $this->execute('roominfo', $room . ':roominfo', array('room' => $room, 'with_ip' => ($with_ip ? 1 : 0)));
	}

	public function setRoomPassword($room, $password = '')
	{
		return $this->execute('setroompassword', $room . ':setroompassword', array('room' => $room, 'password' => $password));
	}

	public function setBroadcastPassword($room, $password = '')
	{
		return $this->execute('setbroadcastpassword', $room . ':setbroadcastpassword', array('room' => $room, 'password' => $password));
	}

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
