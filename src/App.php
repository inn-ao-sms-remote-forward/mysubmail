<?php

declare(strict_types = 1);

namespace InnStudio\MySubMail;

final class App
{
    private $appId = '';

    private $appSecret = '';

    private $sms = '';

    private $phoneNumber = '';

    private $configPath = '';

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;

        $this->setPhoneNumber();
        $this->setSms();
        $this->setConfig();

        if ($this->send()) {
            die(\json_encode([
                'code' => 0,
            ]));
        }

        die(\json_encode([
            'code' => -1,
        ]));
    }

    private function setPhoneNumber(): void
    {
        $this->phoneNumber = (string) \filter_input(\INPUT_GET, 'number', \FILTER_VALIDATE_INT);

        if ( ! $this->phoneNumber) {
            die('Invalid phone number.');
        }
    }

    private function setSms(): void
    {
        $this->sms = (string) \filter_input(\INPUT_GET, 'sms', \FILTER_SANITIZE_STRING);

        if ( ! $this->sms) {
            die('Invalid SMS content.');
        }
    }

    private function send(): bool
    {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, 'https://api.mysubmail.com/message/send.json');
        \curl_setopt($ch, \CURLOPT_POST, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, [
            'appid'     => $this->appId,
            'to'        => $this->phoneNumber,
            'content'   => $this->sms,
            'signature' => $this->appsecret,
        ]);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        $res = \curl_exec($ch);
        \curl_close($ch);

        if ( ! $res) {
            return false;
        }

        return 'success' === (\json_decode($res, true)['status'] ?? '');
    }

    private function setConfig(): void
    {
        if ( ! \is_readable($this->configPath)) {
            die('Invalid config file path.');
        }

        $config = \json_decode((string) \file_get_contents($this->configPath), true);

        if ( ! \is_array($config)) {
            die('Invalid config file content.');
        }

        [
            'appId'     => $this->appId,
            'appSecret' => $this->appSecret,
        ] = $config;
    }
}
