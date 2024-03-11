<?php 
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
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $admins = [
      "652001276" => true,  
      "5722565974" => true,
    ];
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if ($admins[$user -> tg_id]) {
            $bannedus = SubErase(" ".$_GET['obj'], " ", " ");
            $comment = SubErase($_GET['obj']." --===d``fff_f_", $bannedus." ", " --===d``fff_f_");
            if (!$admins[$bannedus]) {
                $userr = R::findOne('meshbotusers', 'tg_id = ?', array($bannedus));
                if ($userr && !$userr -> banglobal) {
                    $userr -> banglobal = true;
                    $userr -> bancomment = $comment;
                    R::store($userr);
                    $message = "⛔️ Ваша учетная запись была заблокированна в глобальном рейтинге!\n\nТеперь вы не можете пользоваться глобальным рейтингом. Если вы не согласны с решением или считаете его ошибочным - обратитесь в поддержку с апелляцией.\n\nКомментарий администрации:\n```\n".$comment."```";
                    sendMessage($userr -> tg_id, $message, null);
                    $message = "🤬 Вы успешно заблокировали пользователя!";
                    sendMessage($user -> tg_id, $message, null);
                }
                elseif ($userr -> banglobal) {
                    $message = "🤬 Вы успешно заблокировали пользователя!";
                    sendMessage($user -> tg_id, $message, null);
                }
            }
        }
    }
    function sendMessage($chatId, $text, $keyboard, $photo = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => 'True',
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
            'disable_web_page_preview' => 'True',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/editMessageText?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>