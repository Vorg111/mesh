<?php
    session_start();
    require "libs/rb.php";
    $token = '6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8';
    function SubErase($string, $start_in, $end_in) {
        $start = strpos($string, $start_in);
        if ($start !== false) {
            $start += strlen($start_in);
            
            $end = strpos($string, $end_in, $start);
            
            if ($end !== false) {
                $substring = substr($string, $start, $end - $start);
                return $substring;
            } else {
                return "Строка ' и названием:' не найдена.";
            }
        } else {
            return "Строка 'chat id: ' не найдена.";
        }
        return $str;
    }
    
    
    function getAuthorizationCode() {
        $authorize_url = "https://twitter.com/i/oauth2/authorize";
        $callback_uri = "https://vorg.site/meshdnevnik_bot/twitter-connect.php";
        $client_id = "N0J3dkYyNHN5anEtVS1kcW9UWWg6MTpjaQ";
        $client_secret = "-8wkXsw2dq_92Dp9_cuRTfEsE0Bf8D5IjuNKuhyIsfy_mO6aZc";
        $str = (pack('H*', hash("sha256", 'challenge')));
        $code_challenge = rtrim(strtr(base64_encode($str), '+/', '-_'), '=');

        $authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id . "&redirect_uri=" . $callback_uri . "&scope=follows.read%20offline.access&state=state&code_challenge=".$code_challenge."&code_challenge_method=plain";
        ?>
            <script>
                window.location.href="<?php echo $authorization_redirect_url; ?>";
            </script>
        <?php
    }
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    if (isset($_GET['chid'])) $userId = $_GET['chid'];
    
    if (!isset($userId)) {
        $userId = $_SESSION['log_to_twitter'];
    }
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if (!isset($user -> twitter)) {
            if (isset($_SESSION['log_to_twitter'])) {
                if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
                    unset($_SESSION['log_to_twitter']);
                    sendMessage($userId, "😢 Упс... Походу вам не удалось подключить свой Твиттер к боту. Скорее всего вы нажали на кнопку отменить при поптку входа через Твиттер. Если хотите, то повторите попытку еще разок, для этого кликните по [ссылке](https://vorg.site/meshdnevnik_bot/twitter-connect.php?chid=".$userId.").", null);
                    ?>
                        <script>
                            window.location.href = "https://t.me/meshdnevnik_bot";
                        </script>
                    <?php
                }
                if (isset($_GET['code'])) {
                    //unset($_SESSION['log_to_twitter']);
                    
                    $consumer_key = '01dc01TXw1bB4vl1jMbe6kvIL';
                    $consumer_secret = 'L7dvdaUp2UERD1pPGHstvHO6ApSmi2MUYTCf4YP2kZJGWfMbLt';
                    
                    
                    print_r($_GET);
                }
            }
            else {
                $_SESSION['log_to_twitter'] = $userId;
                getAuthorizationCode();
            }
        }
    }
        
    function sendMessage($chatId, $text, $keyboard, $photo = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        if ($photo) {
            $data = [
                'chat_id' => $chatId,
                'caption' => $text,
                'photo' => $photo,
                'parse_mode' => 'Markdown',
            ];
            if ($keyboard) {
                $data['reply_markup'] = $keyboard;
            }
            file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendPhoto?' . http_build_query($data));
            return;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendMessage?' . http_build_query($data));
    }
    
    
    function deleteMessage($chatId, $message_id) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/deleteMessage?' . http_build_query($data));
    }
    
    
    function editMessage($chatId, $messageId, $text, $keyboard) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/editMessageText?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения 
?>   