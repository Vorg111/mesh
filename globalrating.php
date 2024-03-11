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
    
    $admins = [
      "652001276" => true,  
      "5722565974" => true,
    ];
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    function backMyName($str) {
        $out = "";
        $spec = [
            "_" => true,
            "`" => true,
            "*" => true,
            "|" => true,
            "^" => true,
        ];
        for ($i = 0; $i < strlen($str); $i++) {
            if ($spec[$str[$i]]) {
                $message = substr($message, 0, -1);
                if ($str[$i] == '_') {
                    $message .= "___\___";
                }
            }
            $message .= $str[$i];
        }
        return $message;
    }
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if ($user -> banglobal) return;
        if (!$user -> in_global) {
            $message = "***🌍 Глобальный рейтинг***\n\nГлобальный рейтинг - рейтинг всех пользователей бота. Нажимая кнопку «Продолжить», вы соглашаетесь с тем, что ваше имя в Telegram будет отображаться всем пользователям, если вы попадете в топ 100 и то, что ваше имя не может никого оскорбить, иначе вы будете заблокированы в глобальном рейтинге.";
            $keyboard = [
              'inline_keyboard' => [
                    [["text" => "Продолжить", "callback_data" => "glrt.setupok.",],],
                    [["text" => "📈", "callback_data" => "rt.full."], ["text" => "📉", "callback_data" => "rt.short."], ["text" => "🏆", "callback_data" => "rt.predmetss."],],
                ],  
            ];
            if (isset($_GET['mid'])) {
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                return;
            }
            sendMessage($userId, $message, json_encode($keyboard));
            return;
        }
        $users = R::findAll('meshbotusers', 'WHERE in_global = true ORDER BY global_ball DESC');
        $message = "***🌍 Глобальный рейтинг***\n\n";
        $j = 0;
        foreach ($users as $i) {
            if ($i -> banglobal) continue;
            $j++;
            if ($j < 21) {
                if ($used[$i -> profile_id]) {
                    //$j--;
                    //continue;
                }
                if ($i -> profile_id == $user -> profile_id) {
                    $message .= ($i -> rating_smile)." ***".$j.". ";
                    $message .= ("Я*** ___(".backMyName($i -> name_global).")___");
                    if ($_GET['shown']) $message .= "*** - ".number_format(log($i -> global_ball), 2, '.', '')."✰***";
                    $used[$i -> profile_id] = true;
                    $message .= "\n";
                    continue;
                }
                $message .= ($i -> rating_smile)." ".$j.". ";
                $message .= ($i -> name_global);
                if ($_GET['shown']) $message .= " - _".number_format(log($i -> global_ball), 2, '.', '')."✰_";
                if ($admins[$user -> tg_id]) {
                    $message .= " `".$i -> tg_id."`";
                }
                $message .= "\n";
                $used[$i -> profile_id] = true;
            }
            else {
                if ($used[$user -> profile_id]) {
                    break;
                }
                if ($i -> profile_id == $user -> profile_id) {
                    $message .= "------------------------------\n";
                    $message .= ($i -> rating_smile)." ***".$j.". ";
                    $message .= ("Я*** ___(".backMyName($i -> name_global).")___");
                    if ($_GET['shown']) $message .= "*** - ".number_format(log($i -> global_ball), 2, '.', '')."✰***";
                    $used[$i -> profile_id] = true;
                    $message .= "\n";
                    continue;
                }
                
            }
        }
        if (!$_GET['shown']) {
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "👁 Показать баллы", "callback_data" => "glrt.shown."],],
                    [["text" => "📈", "callback_data" => "rt.full."], ["text" => "📉", "callback_data" => "rt.short."], ["text" => "🏆", "callback_data" => "rt.subjects."],],
                ],  
            ];
        }
        else {
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "❌ Скрыть баллы", "callback_data" => "glrt.base."],],
                    [["text" => "📈", "callback_data" => "rt.full."], ["text" => "📉", "callback_data" => "rt.short."], ["text" => "🏆", "callback_data" => "rt.subjects."],],
                ],  
            ];
        }
        $message .= "\n[О том, как определяется ваше место в глобальном рейтинге](https://telegra.ph/Globalnyj-rejting-02-02)";
        if (isset($_GET['mid'])) {
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        sendMessage($user -> tg_id, $message, json_encode($keyboard));
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
        echo file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/editMessageText?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>