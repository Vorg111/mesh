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
    
    $littles_numbers = array(
        1 => "",
        2 => "₂",
        3 => "₃",
        4 => "₄",
        5 => "₅",
        6 => "₆",
        7 => "₇",
        8 => "₈",
        9 => "₉",
        10 => "₁₀",
        );
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $users = R::find('meshbotusers');
        
    $date_r = date("Y-m-d");
    
    foreach ($users as $user) {
        $message = "🔗 ***Пригласи друзей и получи подарки:***\n\n";
        $message .= "Помоги боту и пригласи своих друзей в него! \n";
            $message .= "🎉 Вы пригласили ***".(0 + ($user -> refs))."*** друзей!";
            if ($user -> refs < 5) {
                $message .= "\n\nПригласи еще ".(5 - ($user -> refs))." друзей в бота и получи ***Пак \"Транспорт\" (🚠 🚃 🚇 🚀️)***";
                $message .= " эмодзи для рейтинга!";
            }
            $message .= "\n\n___(За приглашенного человека - засчитывается тот человек, который подключил МЭШ к боту)___";
            $message .= "\n\nВаша реферальная ссылка: `https://t.me/meshdnevnik_bot?start=".$user -> tg_id."`";
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => 'Поделиться ботом', 'url' => 'https://t.me/share/url?text=Пользуйся электронным дневником МЭШ внутри Telegram!&url='.urlencode('https://t.me/meshdnevnik_bot?start='.$user -> tg_id)]]
                ]
            ];
            sendMessage($user -> tg_id, $message, json_encode($keyboard));
        
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
    
    function setChannelDesc($chat, $desc) {
        $data = [
            'chat_id' => $chat,
            'description' => $desc,
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/setChatDescription?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>