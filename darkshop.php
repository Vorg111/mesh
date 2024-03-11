<?php
    require "libs/rb.php";
    $token = '6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8';
    $u_key = "live_IVPRE1d8W2ughJxrdRl6-tYDV0r7XCIe9JAHWnuLDk0";
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
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if (array_search($check[$id], explode(" ", ($user -> added_emoji))) != NULL) {
            sendMessage($userId, "💶 Либо ты ошибся, либо ты очень хочешь дать мне деньги.\n\n***Ты уже купил данный товар!***", json_encode($keyboard));
            return;
        }
            
        $shopId = '308789';
        $secretKey = $u_key;
        
        $apiUrl = 'https://api.yookassa.ru/v3/payments';
        $amount = [
            'value' => 30.00,
            'currency' => 'RUB',
        ];
        $confirmation = [
            'type' => 'redirect',
            'return_url' => 'https://t.me/meshdnevnik_bot',
        ];
        $description = "Покупка секретных смайлов (💎 💣 🛡 🔮 🤯 🥶 😶‍🌫 ️ ☘️ 🌳 🍀 🍄 🌝 🌚) 
для МЭШ бота. Пользователь: ".$user -> tg_id.".";
        $data = [
            'amount' => $amount,
            'capture' => true,
            'confirmation' => $confirmation,
            'description' => $description,
            'receipt' => [
                'customer' => [], 
                'items' => [
                    ['description' => $description,
                    'amount' => $amount,
                    'vat_code' => 1,
                    'quantity' => 1]]], 
            'merchant_customer_id' => $userId.'.1.',
        ];
        if (isset($user -> email)) $data['receipt']['customer']['email'] = $user -> email;
        elseif (isset($user -> phone)) $data['receipt']['customer']['phone'] = $user -> phone;
        else return;
        $idempotenceKey = "l".md5 ( implode ( '|', [$data, date('l jS \of F Y h:i:s A')]) );
        $headers = [
            'Idempotence-Key: ' . $idempotenceKey,
            'Content-Type: application/json',
        ];
        
        $ch = curl_init($apiUrl);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Ошибка cURL: ' . curl_error($ch);
        }
        
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        $keyboard = [
            'inline_keyboard' => [
                [['text' => 'Оплатить', 'url' => $data['confirmation']['confirmation_url']],],
            ],
        ];
        
        print_r($data);
        sendMessage($userId, "Darkshop СМАЙЛОВ\n\n😁 Купи серкретные смайлы в рейтинг класса всего за 30₽!\n\nТы получишь доступ к этим эмодзи: 💎 💣 🛡 🔮 🤯 🥶 😶‍🌫 ️ ☘️ 🌳 🍀 🍄 🌝 🌚", json_encode($keyboard));
        //sendInvoice($userId, "Darkshop СМАЙЛОВ", "😁 Купи серкретные смайлы в рейтинг класса всего за 100₽!\n\nТы получишь доступ к этим эмодзи: 💎 💣 🛡 🔮 🤯 🥶 😶‍🌫 ️ ☘️ 🌳 🍀 🍄 🌝 🌚", 100);
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
    
    
    
    function sendInvoice($chatId, $title, $description, $amount) {
        $data = [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $chatId,
            'provider_token' => "390540012:LIVE:45541",
            'currency' => 'RUB',
            'prices' => json_encode([
                    ['label' => 1,
                    'amount' => $amount * 100,],
                ]),
            //'parse_mode' => 'Markdown',
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendInvoice?' . http_build_query($data));
    }
    
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения 
?>   