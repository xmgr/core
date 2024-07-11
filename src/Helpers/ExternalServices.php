<?php
    
    namespace Xmgr\Helpers;
    
    /**
     * This class provides methods for interacting with external services such as Telegram and Gravatar.
     */
    class ExternalServices {
        
        /**
         * Sends a message to a Telegram chat using Telegram API.
         *
         * Example:
         * Input.....: "Hello, world!"
         * Output....: "true" if the message is successfully sent, otherwise an error message will be returned as a
         * string.
         *
         * @param string $message      The message to be sent.
         * @param string $config       Configuration key used to retrieve the API token and chat ID from the
         *                             configuration file. If not provided, default values will be used.
         *
         * @return bool|string Returns "true" if the message is successfully sent, otherwise an error message will be
         *                     returned as a string.
         */
        public static function telegram(string $message, string $config = ''): bool|string {
            $connection = $config ?: config('api.telegram.use', 'default');
            $api_token  = config("api.telegram.connections.$connection.bot_api_token", '');
            $chat_id    = config("api.telegram.connections.$connection.chat_id", '');
            $msg        = urlencode($message);
            
            $url      = "https://api.telegram.org/bot$api_token/sendMessage?chat_id=$chat_id&text=$msg";
            $ch       = curl_init();
            $optArray = [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true,];
            curl_setopt_array($ch, $optArray);
            $result = curl_exec($ch);
            curl_close($ch);
            
            return $result;
        }
        
        /**
         * Returns the URL for a Gravatar image based on the provided email address.
         *
         * @param string      $email    The email address associated with the Gravatar image.
         * @param int|null    $size     Optional. The size of the Gravatar image. Default is 40.
         * @param string|null $fallback Optional. The fallback image to use if the email does not have a Gravatar.
         *
         * @return string  The URL of the Gravatar image.
         */
        public static function get_gravatar_url(string $email, int $size = null, string $fallback = null): string {
            $size               = (abs((int)$size) ?: 40);
            $request_parameters = [];
            if ($fallback) {
                $request_parameters['d'] = urlencode($fallback);
            }
            $request_parameters['s'] = $size;
            
            return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . ($request_parameters ? '?' . http_build_query($request_parameters) : '');
        }
        
    }
