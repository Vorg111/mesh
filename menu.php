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
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if (!isset($_GET['categ'])) {
            $message ="🍱 Меню на сегодня разбито на категории. Вы можете выбрать одну из категорий ниже и вы получите price-лист этой категории на сегодня.";
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "🥨 Выпечка", "callback_data" => "mn.bakery."], ["text" => "🥗 Салаты", "callback_data" => "mn.salats."]],
                    [["text" => "🥪 Сендвичи", "callback_data" => "mn.sandwitch."], ["text" => "🍊 Фрукты", "callback_data" => "mn.fruits."]],
                    [["text" => "🍪 Печенье", "callback_data" => "mn.cookies."], ["text" => "🧃 Напитки", "callback_data" => "mn.water."]],
                    [["text" => "🍡 Мармелад", "callback_data" => "mn.marmelad."], ["text" => "🍬 Конфеты", "callback_data" => "mn.candies."]],
                    [["text" => "🏷 Акция", "callback_data" => "mn.sale-off."], ["text" => "⚫️ Другое", "callback_data" => "mn.other."]],
                ],
            ];
            if (isset($_GET['mid'])) {
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                return;
            }
            sendMessage($userId, $message, json_encode($keyboard));
        }
        else {
            if (isset(json_decode($user -> menu, true)['menu'])) {
                $result2 = json_decode($user -> menu, true);
            }
            else {
                $url = "https://school.mos.ru/api/family/mobile/v1/menu/buffet/?date=".date("Y-m-d")."&contract_id=1";
                    
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familymp"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                
                if (str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                    if (isset($_GET['mid'])) {
                        deleteMessage($userId, $_GET['mid']);
                    }
                    file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                    return;
                }
            }
            $message = "Вот ваше меню в категории";
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "< Назад", "callback_data" => "mn.home."],],
                ],
            ];
            $smiles = array("🧊", "🍯", "🥢", "🍎", "🍱", "🥄");
            if ($_GET['categ'] == "bakery") {
                $message = "🥨 ".$message." выпечка:\n\n";
                $categor = "Выпечка";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "salats") {
                $message = "🥗 ".$message." салаты:\n\n";
                $categor = "САЛАТЫ";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "sandwitch") {
                $message = "🥪 ".$message." сендвичи:\n\n";
                $categor = "Сендвичи";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "fruits") {
                $message = "🍊 ".$message." фрукты:\n\n";
                $categor = "Фрукты";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor) || str_contains($i['group'], "Сухофрукты")) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "cookies") {
                $message = "🍪 ".$message." печенье:\n\n";
                $categor = "ПЕЧЕНЬЕ";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "water") {
                $message = "🧃 ".$message." напитки:\n\n";
                $categor = "Напитки";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor) || str_contains($i['group'], "Соки") || str_contains($i['group'], "Коктейли")) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "marmelad") {
                $message = "🍡 ".$message." мармелад:\n\n";
                $categor = "Мармелад";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "candies") {
                $message = "🍬 ".$message." конфеты:\n\n";
                $categor = "Конфеты";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "sale-off") {
                $message = "🏷 ".$message." акция:\n\n";
                $categor = "Акция";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (str_contains($i['group'], $categor)) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            }
            else if ($_GET['categ'] == "other") {
                $message = "⚫️ ".$message." другое:\n\n";
                foreach ($result2['menu'][0]['items'] as $i) {
                    if (!str_contains($i['group'], "Акция") && !str_contains($i['group'], "Конфеты") &&
                    !str_contains($i['group'], "Мармелад") && !str_contains($i['group'], "Напитки") &&
                    !str_contains($i['group'], "ПЕЧЕНЬЕ") && !str_contains($i['group'], "Фрукты") &&
                    !str_contains($i['group'], "Сендвичи") && !str_contains($i['group'], "САЛАТЫ") &&
                    !str_contains($i['group'], "Выпечка") && !str_contains($i['group'], "Соки") &&
                    !str_contains($i['group'], "Коктейли") && !str_contains($i['group'], "Сухофрукты")) {
                        $message .= "🧊 ".$i['name']." - ***".($i['price'] / 100)."₽***\n";
                    }
                }
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
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