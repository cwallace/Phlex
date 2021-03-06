<?php

require_once("CCprotoBuf.php");
require_once("CCDefaultMediaPlayer.php");
require_once("mdns.php");

class Chromecast
{

	// Sends a picture or a video to a Chromecast using reverse
	// engineered castV2 protocol

	public $socket; // Socket to the Chromecast
	public $requestId = 1; // Incrementing request ID parameter
	public $transportid = ""; // The transportid of our connection
	public $sessionid = ""; // Session id for any media sessions
	public $DMP; // Represents an instance of the Default Media Player.
	public $lastip = ""; // Store the last connected IP
	public $lastport; // Store the last connected port
	public $lastactivetime; // store the time we last did something

	public function __construct($ip, $port) {

		// Establish Chromecast connection

		// Don't pay much attention to the Chromecast's certificate.
		// It'll be for the wrong host address anyway if we
		// use port forwarding!
		$contextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			]
		];
		$context = stream_context_create($contextOptions);

		if ($this->socket = stream_socket_client('ssl://' . $ip . ":" . $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context)) {
		} else {
			throw new Exception("Failed to connect to remote Chromecast");
		}

		$this->lastip = $ip;
		$this->lastport = $port;

		$this->lastactivetime = time();

		// Create an instance of the DMP for this CCDefaultMediaPlayer
		$this->DMP = new CCDefaultMediaPlayer($this);
	}

	public static function scan() {
		// Performs an mdns scan of the network to find chromecasts and returns an array
		// Let's test by finding Google Chromecasts
		$mdns = new mDNS();
		// Search for chromecast devices
		// For a bit more surety, send multiple search requests
		$mdns->query("_googlecast._tcp.local",1,12,"");
		$mdns->query("_googlecast._tcp.local",1,12,"");
		$mdns->query("_googlecast._tcp.local",1,12,"");
		$cc = 20;
		$chromecasts = array();
		while ($cc>0) {
			$inpacket = $mdns->readIncoming();
			//$mdns->printPacket($inpacket);
			// If our packet has answers, then read them
			if ($inpacket->packetheader->getAnswerRRs()> 0) {
				for ($x=0; $x < sizeof($inpacket->answerrrs); $x++) {
					if ($inpacket->answerrrs[$x]->qtype == 12) {
						//print_r($inpacket->answerrrs[$x]);
						if ($inpacket->answerrrs[$x]->name == "_googlecast._tcp.local") {
							$name = "";
							for ($y = 0; $y < sizeof($inpacket->answerrrs[$x]->data); $y++) {
								$name .= chr($inpacket->answerrrs[$x]->data[$y]);
							}
							// The chromecast name is in $name. Send a a SRV query
							$mdns->query($name, 1, 33, "");
							$mdns->query($name, 1, 16, "");
							$cc=60;
						}
					}
						if ($inpacket->answerrrs[$x]->qtype == 16) {

							// get target name
							$offset = 6;
							$offset++;
							$target = "";
							for ($z=0; $z < $size; $z++) {
								$target .= chr($d[$offset + $z]);
							}
							$target .= ".local";

							// get friendly name
							$a = $inpacket->answerrrs[$x];

							$string = "";
							for ($x=0; $x < sizeof($a->data); $x++) {
								$string .= chr($a->data[$x]);
							}
							$start = 'fn=';
							$end = 'ca=';
							$string = ' ' . $string;
							$ini = strpos($string, $start);
							if ($ini == 0) return '';
							$ini += strlen($start);
							$len = strpos($string, $end, $ini) - $ini;
							$fname = substr($string, $ini, $len-1);

							// Loop through the chromecasts and fill in friendly name
							foreach ($chromecasts as $key=>$value) {
								if ($value['target'] == $target) {
									$value['fname'] = $fname;
									$chromecasts[$key] = $value;
								}
							}
							$cc=60;
						}
					if ($inpacket->answerrrs[$x]->qtype == 33) {
						$d = $inpacket->answerrrs[$x]->data;
						$port = ($d[4] * 256) + $d[5];
						// We need the target from the data
						$offset = 6;
						$size = $d[$offset];
						$offset++;
						$target = "";
						for ($z=0; $z < $size; $z++) {
							$target .= chr($d[$offset + $z]);
						}
						$target .= ".local";
						$chromecasts[$inpacket->answerrrs[$x]->name] = array("port"=>$port, "ip"=>"", "target"=>$target);
						// We know the name and port. Send an A query for the IP address
						$mdns->query($target,1,1,"");
						$cc=60;
					}
					if ($inpacket->answerrrs[$x]->qtype == 1) {
						$d = $inpacket->answerrrs[$x]->data;
						$ip = $d[0] . "." . $d[1] . "." . $d[2] . "." . $d[3];
						// Loop through the chromecasts and fill in the ip
						foreach ($chromecasts as $key=>$value) {
							if ($value['target'] == $inpacket->answerrrs[$x]->name) {
								$value['ip'] = $ip;
								$chromecasts[$key] = $value;
							}
						}
					}
				}
			}
			$cc--;
		}

		return $chromecasts;
	}

	function testLive() {
		// If there is a difference of 10 seconds or more between $this->lastactivetime and the current time, then we've been kicked off and need to reconnect
		if ($this->lastip == "") { return; }
		$diff = time() - $this->lastactivetime;
		if ($diff > 9) {
			// Reconnect
			$contextOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
				]
			];
			$context = stream_context_create($contextOptions);
			if ($this->socket = stream_socket_client('ssl://' . $this->lastip . ":" . $this->lastport, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context)) {
			} else {
				throw new Exception("Failed to connect to remote Chromecast");
			}
			$this->cc_connect(1);
			$this->connect(1);
		}
	}

	function cc_connect($tl=0) {
		// CONNECT TO CHROMECAST
		// This connects to the chromecast in general.
		// Generally this is called by launch($appid) automatically upon launching an app
		// but if you want to connect to an existing running application then call this first,
		// then call getStatus() to make sure you get a transportid.
		if ($tl == 0) { $this->testLive(); };
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.tp.connection";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"CONNECT"}';
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
	}

	public function launch($appid) {

		// Launches the chromecast app on the connected chromecast

		// CONNECT
		$this->cc_connect();


		// LAUNCH
		$c = new CastMessage();
                $c->source_id = "sender-0";
                $c->receiver_id = "receiver-0";
                $c->urnnamespace = "urn:x-cast:com.google.cast.receiver";
                $c->payloadtype = 0;
                $c->payloadutf8 = '{"type":"LAUNCH","appId":"' . $appid . '","requestId":' . $this->requestId . '}';
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;

		while ($this->transportid == "") {
			$this->getCastMessage();
		}
	}


	function getStatus() {
		// Get the status of the chromecast in general and return it
		// also fills in the transportId of any currently running app
		$this->testLive();
		$c = new CastMessage();
		$c->source_id = "sender-0";
                $c->receiver_id = "receiver-0";
                $c->urnnamespace = "urn:x-cast:com.google.cast.receiver";
                $c->payloadtype = 0;
                $c->payloadutf8 = '{"type":"GET_STATUS","requestId":' . $this->requestId . '}';
		$c = fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
		$r = "";
		while ($this->transportid == "") {
			$r = $this->getCastMessage();
		}
		return $r;
	}

	function connect($tl = 0) {
	// This connects to the transport of the currently running app
	// (you need to have launched it yourself or connected and got the status)
	if ($tl == 0) { $this->testLive(); };
	$c = new CastMessage();
                $c->source_id = "sender-0";
                $c->receiver_id = $this->transportid;
                $c->urnnamespace = "urn:x-cast:com.google.cast.tp.connection";
                $c->payloadtype = 0;
                $c->payloadutf8 = '{"type":"CONNECT"}';
                fwrite($this->socket, $c->encode());
                fflush($this->socket);
		$this->lastactivetime = time();
                $this->requestId++;
	}

	public function getCastMessage() {
		// Get the Chromecast Message/Response
		// Later on we could update CCprotoBuf to decode this
		// but for now all we need is the transport id  and session id if it is
		// in the packet and we can read that directly.
		$this->testLive();
		$response = fread($this->socket, 2000);
		while (preg_match("/urn:x-cast:com.google.cast.tp.heartbeat/",$response) && preg_match("/\"PING\"/",$response)) {
			$this->pong();
			sleep(3);
			$response = fread($this->socket, 2000);
		}
		if (preg_match("/transportId/s", $response)) {
			preg_match("/transportId\"\:\"([^\"]*)/",$response,$matches);
			$matches = $matches[1];
			$this->transportid = $matches;
		}
		if (preg_match("/sessionId/s", $response)) {
			preg_match("/\"sessionId\"\:\"([^\"]*)/",$response,$r);
			$this->sessionid = $r[1];
		}
		return $response;
	}

	public function sendMessage($urn,$message) {
		// Send the given message to the given urn
		$this->testLive();
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = $this->transportid;
		// Override - if the $urn is urn:x-cast:com.google.cast.receiver then
		// send to receiver-0 and not the running app
		if ($urn == "urn:x-cast:com.google.cast.receiver") { $c->receiver_id = "receiver-0"; }
		if ($urn == "urn:x-cast:com.google.cast.tp.connection") { $c->receiver_id = "receiver-0"; }
		$c->urnnamespace = $urn;
		$c->payloadtype = 0;
		$c->payloadutf8 = $message;
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
		$response = $this->getCastMessage();
		return $response;
	}

	public function pingpong() {
		// Officially you should run this every 5 seconds or so to keep
		// the device alive. Doesn't seem to be necessary if an app is running
		// that doesn't have a short timeout.
		$c = new CastMessage();
                $c->source_id = "sender-0";
                $c->receiver_id = "receiver-0";
                $c->urnnamespace = "urn:x-cast:com.google.cast.tp.heartbeat";
                $c->payloadtype = 0;
                $c->payloadutf8 = '{"type":"PING"}';
                fwrite($this->socket, $c->encode());
                fflush($this->socket);
		$this->lastactivetime = time();
                $this->requestId++;
		$response = $this->getCastMessage();
	}

	public function pong() {
		// To answer a pingpong
		$c = new CastMessage();
                $c->source_id = "sender-0";
                $c->receiver_id = "receiver-0";
                $c->urnnamespace = "urn:x-cast:com.google.cast.tp.heartbeat";
                $c->payloadtype = 0;
                $c->payloadutf8 = '{"type":"PONG"}';
				fwrite($this->socket, $c->encode());
				fflush($this->socket);
				$this->lastactivetime = time();
                $this->requestId++;
	}

}

?>
