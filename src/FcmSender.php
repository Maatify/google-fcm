<?php
/**
 * @copyright   ©2023 Maatify.dev
 * @Liberary    Logger
 * @Project     Logger
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2023-07-25 3:48 PM
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/FCM  view project on GitHub
 * @link        https://github.com/Maatify/Logger/ (maatify/logger),
 * @link        https://github.com/kreait/firebase-php/ (kreait/firebase-php),
 * @copyright   ©2023 Maatify.dev
 * @note        This Project using for Google Firebase Cloud Message.
 * @note        This Project extends other libraries kreait/firebase-php, maatify/logger
 *
 * @note        This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

namespace Maatify\FCM;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Maatify\Logger\Logger;

class FcmSender
{
    private array $notification;
    private array $data;
    private Messaging $messaging;

    public function __construct(string $firebase_credentials_json, array $notification, array $data)
    {
        $this->messaging = (new Factory())
            ->withServiceAccount($firebase_credentials_json)
            ->createMessaging();
        $this->notification = $notification;
        $this->data = $data;
    }

    public function ToMultipleDevicesToken(array $devices_token): array
    {
        $messages = array();
        foreach ($devices_token as $device_token){
            $messages[] = $this->MessageHandler($device_token);
        }
        try {
            return (array)$this->messaging->sendAll($messages);
        } catch (MessagingException|FirebaseException $e) {
            Logger::RecordLog($e, 'fcm_error');
            return [];
        }
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function ToDeviceToken(string $device_token): array
    {
        $message = $this->MessageHandler($device_token);
        return $this->messaging->send($message);
    }

    private function MessageHandler(string $device_token): CloudMessage
    {
        $array['token'] = $device_token;
        if(!empty($this->notification)){
            $array['notification'] = $this->notification;
        }
        if(!empty($this->data)){
            foreach ($this->data as $key => $value){
                if(is_array($value)){
                    print_r($value);
                    $array['data'][$key] = $this->JsonFormat($value);
                }else{
                    $array['data'][$key] = $value;
                }
            }
        }
        return CloudMessage::fromArray($array);
    }

    private function JsonFormat($array): string
    {
        return (string)str_replace(array("\r", "\n"), '', json_encode($array, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES));
    }
}