<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Usage: $this->ios->to('DEVICE_ID')->badge(3)->message('Hello world');
class Ios
{
	private $host = 'gateway.sandbox.push.apple.com';
	private $port = 2195;
	private $cert;
	
	private $device = NULL;
	private $message = NULL;
	private $badge = NULL;
	private $sound = 'default';
	
	private $_CI;
	
	public function __construct()
	{
		$this->_CI =& get_instance();
		
		$this->_CI->config->load('ios', TRUE);

		$config = $this->config->item('ios');
		
		foreach ($config as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	public function to($device)
	{
		$this->device = $device;
		
		return $this;
	}
	
	public function message($message)
	{
		$this->message = urlencode($message);
		
		return $this;
	}
	
	public function badge($badge = 1)
	{
		$this->badge = $badge;
		
		return $this;
	}
	
	public function sound($sound = 'default')
	{
		$this->sound = $sound;
		
		return $this;
	}

	public function send()
	{
		// Build the payload
		$payload['aps'] = array('alert' => $this->message, 'badge' => $this->badge, 'sound' => $this->sound);
		$payload = json_encode($payload);
		
		$stream_context = stream_context_create();
		stream_context_set_option($stream_context, 'ssl', 'local_cert', $this->cert);

		$apns = stream_socket_client('ssl://' . $this->host . ':' . $this->port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);

		$message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $this->device)) . chr(0) . chr(strlen($payload)) . $payload;
		fwrite($apns, $message);

		//socket_close($apns);
		fclose($apns);
	}
}