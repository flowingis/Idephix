<?php

namespace Idephix\Extension\Slack;

use Idephix\IdephixInterface;
use Idephix\Extension\IdephixAwareInterface;

/**
 * Description of Slack wrapper
 *
 * @author dymissy
 */
class Slack implements IdephixAwareInterface
{
    /**
     * @var \Idephix\IdephixInterface
     */
    private $idx;

    private $settings;

    public function __construct($args = array())
    {
        $defaults = array(
            'url' => '',
            'channel' => '#general',
            'icon_url' => 'https://slack.com/img/icons/app-57.png',
            'username' => 'slackbot'
        );

        $this->settings = array_merge($defaults, $args);
    }

    public function sendToSlack($message, $attachments = array(), $channel = '', $icon_url = '', $username = '')
    {
        if (!$channel) {
            $channel = $this->settings['channel'];
        }

        if (!$icon_url) {
            $icon_url = $this->settings['icon_url'];
        }

        if (!$username) {
            $username = $this->settings['username'];
        }

        $data = 'payload=' . json_encode(array(
                'channel' => $channel,
                'text' => $message,
                'icon_url' => $icon_url,
                'unfurl_links' => true,
                'username' => $username,
                'attachments' => $attachments
            ));

        $url = $this->settings['url'];
        $response = $this->send($data, $url);

        if ($response != 'ok') {
            throw new \Exception('Unable to send the message to Slack. The error returned is: ' . $response);
        }

        $this->idx->local("echo 'Message sent to slack channel'");

        return $response;
    }

    protected function send($data, $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }
}
