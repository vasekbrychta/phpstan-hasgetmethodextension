<?php

declare(strict_types=1);

/**
 * @author Vasek Brychta <vaclav@brychtovi.cz>
 */

namespace VasekBrychta;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Message;

class SmsManager
{
	/** @var string */
	private $apiKey;
	/** @var string */
	private $url;

	public function __construct(string $url, string $apiKey)
	{
		$this->apiKey = $apiKey;
		$this->url = $url;
	}

	public function sendSms(string $number, string $message): bool
	{
		$client = new Client();
		try
		{
			$response = $client->post($this->url,
					['form_params' => ['apikey' => $this->apiKey, 'number' => $number, 'message' => $message, 'gateway' => 'high']]);
			if ($response->getStatusCode() === 200)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (RequestException $e)
		{
			$status = 'exception';
			if ($e->hasResponse())
				$status .= ': ' . Message::toString($e->getResponse());

			// log $status
			return false;
		}
	}
}
