<?php 
    require "libs/rb.php";
    $token = '6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8';
    
    
    $keyboard = [
        'keyboard' => [
            [['text' => '📗 Задания'], ['text' => '🗓 Расписание'],],
            [['text' => '🏅 Оценки'], ['text' => '📈 Рейтинг'],],
            [['text' => '🥘 Меню'], ['text' => '🫥 Поддержка'],],
            [['text' => '💾 Портфолио'], ['text' => '⚙️ Настройки'],],
            [['text' => '🔗 Поделиться ботом'],],
        ], 
        'one_time_keyboard' => false,
        'resize_keyboard' => true,
    ];
    
    function createInvoice($user, $name, $description, $id, $price, $fulldesc = NULL) {
        $check = [
           0 => "🖱", 1 => "💎", 2 => "🛟", 3 => "👑", 4 => "🤎",
           5 => "🐼", 6 => "🥥", 7 => "🐊", 8 => "🍪", 9 => "🫁",
           10 => "👓",
        ];
        if (array_search($check[$id], explode(" ", ($user -> added_emoji))) != NULL) {
            $keyboard = [
                'inline_keyboard' => [
                    [["text" => "← Назад", "callback_data" => "st.shop."]],
                ],
            ];
            sendMessage($user -> tg_id, "💶 Либо ты ошибся, либо ты очень хочешь дать мне деньги.\n\n***Ты уже купил данный товар!***", json_encode($keyboard));
            return;
        }
        
        $shopId = '308789';
        $secretKey = "live_IVPRE1d8W2ughJxrdRl6-tYDV0r7XCIe9JAHWnuLDk0";
        
        $apiUrl = 'https://api.yookassa.ru/v3/payments';
        $amount = [
            'value' => $price,
            'currency' => 'RUB',
        ];
        $confirmation = [
            'type' => 'redirect',
            'return_url' => 'https://t.me/meshdnevnik_bot',
        ];
        $desc = $description;
        $description = $description." Пользователь: ".$user -> tg_id.".";
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
            'merchant_customer_id' => $user -> tg_id.'.'.$id.'.',
        ];
        if (isset($user -> email)) $data['receipt']['customer']['email'] = $user -> email;
        elseif (isset($user -> phone)) $data['receipt']['customer']['phone'] = $user -> phone;
        else {
            $message = "🐼 Похоже, что вы очень анонимный пользователь МЭШ, и у вас не привязаны к нему не телефон, не эл. почта. Так что для отправки чека мне требуется твой телефон!";
            $keyboard = [
                'keyboard' => [
                        [["text" => "📞 Поделиться контактом", "request_contact" => true]],
                        [["text" => "← Назад"]],
                    ],
                    'resize_keyboard' => true,
                    
            ];
            sendMessage($user -> tg_id, $message, json_encode($keyboard));
            return;
        }
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
                [['text' => '💳 Оплатить', 'url' => $data['confirmation']['confirmation_url']],],
            ],
        ];
        
        if ($fulldesc != NULL) {
            $desc = $fulldesc;
        }
        sendMessage($user -> tg_id, "💥 $name\n🔹 $desc\n\n💰 Цена: ***$price"."₽***", json_encode($keyboard));
    }

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
    
    function checkName($str) {
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
                $message .= "\\";
            }
            $message .= $str[$i];
        }
        return $message;
    }
    
    // Получите обновление от Telegram
    $update = json_decode(file_get_contents('php://input'), true);
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
        
    $chatId = $update['message']['from']['id'];
    $usrId = $update['callback_query']['message']['chat']['id'];
    if (R::findOne('meshbotparents', 'tg_id = ?', array($usrId))) {
        $url = "https://vorg.site/meshdnevnik_bot/parent/webhook.php";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "content-type application/json",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result2 = json_decode(curl_exec($ch), true);
        return;
    }
    if (R::findOne('meshbotparents', 'tg_id = ?', array($chatId))) {
        $url = "https://vorg.site/meshdnevnik_bot/parent/webhook.php";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "content-type application/json",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result2 = json_decode(curl_exec($ch), true);
        return;
    }
        
    if (isset($update['message']['successful_payment'])) {
        $chatId = $update['message']['from']['id'];
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($chatId));
        if ($user) {
            $shop = [
               "Покупка Пак \"Техника\"" => " ⌚️ 📱 🖥 🖱 💽 📀 🎞 ☎️  📺 📠 📽 ⏰",
               "Darkshop СМАЙЛОВ" => " 💎 💣 🛡 🔮 🤯 🥶 😶‍🌫  ☘️ 🌳 🍀 🍄 🌝 🌚",
               "Покупка Пак \"Туризм\"" => " 🛟 🚢 🎢 🎡 🏟 ⛱ 🗽 ⛺️  🗺",
               "Покупка Пак \"Королевский\"" => " 👑 🫅 🤴🏿 🧜🏻‍♂️ 🪤 💸",
               "Покупка Пак \"Сердца\"" => " 🩷 ❤️ 🧡 💛 💚 🩵 💙 💜 🖤 🩶 🤍 🤎 ❤️‍🔥 💕",
            ];
            sendMessage($chatId, "🛒 ***пасибо за покупку!***\n\nМы выдали вам ".SubErase($update['message']['successful_payment']['invoice_payload']."|", '.', "|")." стоимостью ".
            ($update['message']['successful_payment']['total_amount'] / 100). "RUB!", $keyboard);
            $user -> added_emoji .= $shop[SubErase($update['message']['successful_payment']['invoice_payload']."|", '.', "|")];
            R::store($user);
        }
    }
    if (isset($update['pre_checkout_query'])) {
        $chatId = $update['pre_checkout_query']['from']['id'];
        $shop = [
           "Покупка Пак \"Техника\"" => "⏰",
           "Darkshop СМАЙЛОВ" => "💎",
           "Покупка Пак \"Туризм\"" => "🛟",
           "Покупка Пак \"Королевский\"" => "👑",
           "Покупка Пак \"Сердца\"" => "🩷",
        ];
        if (R::findOne('meshbotusers', 'tg_id = ?', array($chatId))) {
            if (array_search($shop[SubErase($update['pre_checkout_query']['invoice_payload']."|", '.', "|")], explode(" ", (R::findOne('meshbotusers', 'tg_id = ?', array($chatId)) -> added_emoji))) != NULL) {
                answerPreCheckoutQuery($update['pre_checkout_query']['id'], false, "\n💶 Либо ты ошибся, либо ты очень хочешь дать мне деньги.\n\nТы уже купил данный товар");
                return;
            }
            answerPreCheckoutQuery($update['pre_checkout_query']['id'], true);
        }
        else {
            answerPreCheckoutQuery($update['pre_checkout_query']['id'], false);
        }
        return;
    }    
    if (isset($update['callback_query'])) {
        $chatId = $update['callback_query']['message']['chat']['id'];
        
        if (R::findOne('meshbotusers', 'tg_id = ?', array($chatId))) {
            $callback_data = $update['callback_query']['data'];
            if (str_contains($callback_data, "dz.")) {
                $object = SubErase($callback_data, "dz.", ".");
                $today = new DateTime();
                $today->modify('+1 day');
                $tomorrow = $today->format('Y-m-d');
                if ($object == "nextday") {
                    $dateString = SubErase($callback_data, "nextday.", "|");
                    $today = DateTime::createFromFormat('Y-m-d', $dateString);
                    $today->modify('+1 day');
                    $tomorrow = $today->format('Y-m-d');
                    //sendMessage($chatId, "https://vorg.site/meshdnevnik\_bot/dz.php?chid=".$chatId."&date=$tomorrow&mid=".$update['callback_query']['message']['message_id'], null);
                    file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId."&date=$tomorrow&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "prevday") {
                    $dateString = SubErase($callback_data, "prevday.", "|");
                    $today = DateTime::createFromFormat('Y-m-d', $dateString);
                    $today->modify('-1 day');
                    $day = $today->format('Y-m-d');
                    if ($day == $tomorrow) {
                        $object = "tommorow";
                    }
                    else {
                        file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId."&date=$day&mid=".$update['callback_query']['message']['message_id']);
                    }
                }
                if ($object == "tommorow") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId."&date=next&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "today") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
            }
            if (str_contains($callback_data, "sc.")) {
                $object = SubErase($callback_data, "sc.", ".");
                file_get_contents("https://vorg.site/meshdnevnik_bot/Schedule.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']."&date=".$object);
            }
            if (str_contains($callback_data, "mr.")) {
                $object = SubErase($callback_data, "mr.", ".");
                if ($object == "nextday") {
                    $dateString = SubErase($callback_data, "nextday.", "|");
                    $today = DateTime::createFromFormat('Y-m-d', $dateString);
                    $today->modify('+1 day');
                    $tomorrow = $today->format('Y-m-d');
                    file_get_contents("https://vorg.site/meshdnevnik_bot/marks.php?chid=".$chatId."&date=$tomorrow&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "prevday") {
                    $dateString = SubErase($callback_data, "prevday.", "|");
                    $today = DateTime::createFromFormat('Y-m-d', $dateString);
                    $today->modify('-1 day');
                    $day = $today->format('Y-m-d');
                    file_get_contents("https://vorg.site/meshdnevnik_bot/marks.php?chid=".$chatId."&date=$day&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "avg") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/marks_avg.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "predmets") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/predmet_marks.php?animation=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "predmetss") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/predmet_marks.php?animation=0&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "predmet") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/predmet_marks.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']."&pid=".SubErase($callback_data, "predmet.", "."));
                }
                if ($object == "days") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/marks.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
            }
            if (str_contains($callback_data, "mn.")) {
                $object = SubErase($callback_data, "mn.", ".");
                if ($object == "home") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/menu.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/menu.php?categ=$object&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
            }
            if (str_contains($callback_data, "rt.") && !str_contains($callback_data, "glrt.")) {
                $object = SubErase($callback_data, "rt.", ".");
                if ($object == "nonvisible") {
                    $object = "main";
                }
                if ($object == "visble") {
                    $object = "main";
                    $userr = R::findOne('meshbotusers', 'tg_id = ?', array($chatId));
                    $userr -> rating_visible = true;
                    R::store($userr);
                }
                if ($object == "full") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/rating_full.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                if ($object == "main" || $object == "short") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/rating.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                if ($object == "predmets") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/subject_rating.php?animation=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                if ($object == "subjects") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/subject_rating.php?animation=0&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                if ($object == "only_my_subjects") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/subject_rating.php?only=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                    return;
                }
                if ($object == "subject") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/subject_rating.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']."&pid=".SubErase($callback_data, "subject.", "."));
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/menu.php?categ=$object&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
            }
            if (str_contains($callback_data, "st.")) {
                $object = SubErase($callback_data, "st.", ".");
                //sendMessage($chatId, "https://vorg.site/meshdnevnik\_bot/settings.php?$object=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id'], null);
                file_get_contents("https://vorg.site/meshdnevnik_bot/settings.php?$object=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                return;
            }
            if (str_contains($callback_data, "glrt.")) {
                $object = SubErase($callback_data, "glrt.", ".");
                if ($object == "setupok") {
                    $userr = R::findOne('meshbotusers', 'tg_id = ?', array($chatId));
                    $name = $update['callback_query']['from']['first_name'];
                    if (isset($update['callback_query']['from']['last_name'])) $name .= " ".$update['callback_query']['from']['last_name'];
                    $userr -> in_global = true;
                    $userr -> name_global = checkName($name);
                    R::store($userr);
                    if ($userr -> global_ball < 100) {
                        $ball = file_get_contents("https://vorg.site/meshdnevnik_bot/get-global_ball.php?chid=".$userr -> tg_id);
                        if ((double)$ball > 0) {
                            $userr -> global_ball = (double)$ball;
                            R::store($userr);
                        }
                    }
                    file_get_contents("https://vorg.site/meshdnevnik_bot/globalrating.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "shown") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/globalrating.php?shown=1&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "base") {
                    //sendMessage("652001276", "https://vorg.site/meshdnevnik\_bot/globalrating.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id'], null);
                    file_get_contents("https://vorg.site/meshdnevnik_bot/globalrating.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                return;
            }
            if (str_contains($callback_data, "pt.")) {
                $object = SubErase($callback_data, "pt.", ".");
                if ($object == "diag") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/diagnostics.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "sport") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/sport.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if ($object == "ol") {
                    file_get_contents("https://vorg.site/meshdnevnik_bot/olimp.php?chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                if (str_contains($object, "ol")) {
                    $object = SubErase($callback_data, "pt.ol", ".");
                    file_get_contents("https://vorg.site/meshdnevnik_bot/olimp.php?start=".$object."&chid=".$chatId."&mid=".$update['callback_query']['message']['message_id']);
                }
                return;
            }
            return;
        }
    }
    
    $replyToMessageId = $update['message']['reply_to_message']['message_id'];
    $replyToMessage = $update['message']['reply_to_message']['text'];
    if (isset($replyToMessageId) && $update['message']['reply_to_message']['from']['is_bot'] == "true") {
        if (isset($update['message']['contact']) && str_contains($replyToMessage, " телефон, не эл. почта. Так что для отправки чека мне требуется твой телефон!")) {
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];
            $messageId = $update['message']['message_id'];
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            $user -> phone = $update['message']['contact']['phone_number'];
            R::store($user);
            deleteMessage($userId, $messageId);
            deleteMessage($userId, $replyToMessageId);
            sendMessage($userId, "👍🏽 ***Вы успешно привязали свой номер телефона к боту!***\nМожете возвращаться к покупкам!", json_encode($keyboard));
            return;
        }
        if (str_contains($replyToMessage, 'Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанны')) {
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];
            $messageId = $update['message']['message_id'];
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            
            $messageText = $update['message']['text'];
            
            $str = " ";
            $str .= $user -> added_emoji;
            
            $smiles = explode(" ", $str);
            
            if (array_search($messageText, $smiles) != NULL) {
                deleteMessage($chatId, $messageId);
                $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\nВы успешно поменяли свой смайл на ".$messageText."!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= $user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                $user -> rating_smile = $messageText;
                R::store($user);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                sendMessage($userId, ($messageText), null);
                return;
            }
            else if ($messageText == "👽") {
                deleteMessage($chatId, $messageId);
                $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\nНу и любишь ты секреты, поздравляю с секретным смайлом! Вы успешно поменяли свой смайл на ".$messageText."!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= "👽 ".$user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                $user -> added_emoji .= " 👽";
                $user -> rating_smile = $messageText;
                R::store($user);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                sendMessage($userId, ($messageText), null);
                return;
            }
            else if ($messageText == "😻") {
                deleteMessage($chatId, $messageId);
                $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\nНу и любишь ты секреты, поздравляю с секретным смайлом! Вы успешно поменяли свой смайл на ".$messageText."!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= "😻 ".$user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                $user -> added_emoji .= " 😻";
                $user -> rating_smile = $messageText;
                R::store($user);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                sendMessage($userId, ($messageText), null);
                return;
            }
            else if ($messageText == "😴") {
                deleteMessage($chatId, $messageId);
                $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\nНу и любишь ты секреты, поздравляю с секретным смайлом! Вы успешно поменяли свой смайл на ".$messageText."!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= "😴 ".$user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                $user -> added_emoji .= " 😴";
                $user -> rating_smile = $messageText;
                R::store($user);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                sendMessage($userId, ($messageText), null);
                return;
            }
            else if ($messageText == "🤖") {
                deleteMessage($chatId, $messageId);
                $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\nНу и любишь ты секреты, поздравляю с секретным смайлом! Вы успешно поменяли свой смайл на ".$messageText."!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= "🤖 ".$user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                $user -> added_emoji .= " 🤖";
                $user -> rating_smile = $messageText;
                R::store($user);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                sendMessage($userId, ($messageText), null);
                return;
            }
            else {
                $message = "😓 Упс... Что-то явно пошло не по плану, вам надо отправить ***ТОЛЬКО*** смайлик ответом на это сообщение! Приэтом он должен присутствовать в списке ниже!\n\n\n";
                $message .= "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n***";
                $message .= $user -> added_emoji."***\n\n";
                $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
                $keyboard = [
                  "inline_keyboard" => [
                      [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                      [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                      [["text" => "← Назад", "callback_data" => "st.rating."]],
                    ],  
                ];
                deleteMessage($chatId, $messageId);
                editMessage($chatId, $replyToMessageId, $message, json_encode($keyboard));
                return;
            }
        }
    }
        
    $chatId = $update['message']['chat']['id'];
    $userId = $update['message']['from']['id'];  
    $messageText = $update['message']['text'];
    
    if (str_contains($messageText, "/start ")) {
        if (str_contains($messageText, "/start uc")) {
            $object = SubErase($messageText, "/start uc", "000165");
            $object2 = SubErase($messageText."//fff5324%^b36$", "000165", "//fff5324%^b36$");
            file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId."&date=$object&cdz=1&g_k=".$object2);
            return;
        }
        $object = SubErase($messageText."//fff5324%^b36$", "/start ", "//fff5324%^b36$");
        if (!R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
            $create_keyboard = [
                'inline_keyboard' => [
                    [["text" => "Войти с МЭШ", "url" => "https://vorg.site/meshdnevnik_bot/mesh_redirect.php?ref=".$object."&id=".$userId],],
                    [['text' => "Политика конфидециальности", "url" => "https://vorg.site/meshdnevnik_bot/privacy.pdf"],],
                    [['text' => "Пользовательское соглашение", "url" => "https://vorg.site/meshdnevnik_bot/termofuse.pdf"],],
                ],
            ];
            if (str_contains($messageText, "promo")) {
                $_gSESSION['promo_id'] = $object;
                if (isset($_gSESSION['promo_id'])) {
                    $promo = R::findOne('meshbotpromo', 'promo_id = ?', array($_gSESSION['promo_id']));
                    if (isset($promo)) {
                        $promo -> countin++;
                        R::store($promo);
                    }
                }
                $create_keyboard['inline_keyboard'][0][0]["url"] = "https://vorg.site/meshdnevnik_bot/mesh_redirect.php?promo=".$object."&id=".$userId;
            }
            //sendMessage($chatId, $message, null);
            sendMessage($chatId, "Здравствуйте. Это бот, который поможет вам пользоваться МЭШ в телеграме. Для использования пожалуйста пройдите авторизацию через МЭШ по кнопке ниже:", json_encode($create_keyboard));
            return;
        }
        $messageText = "/start";
    }
    if ($messageText == "← Назад") {
        deleteMessage($userId, $update['message']['message_id']);
    }
        
    if ($messageText == "/start" || $messageText == "← Назад") {
        if (!R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
            $create_keyboard = [
                'inline_keyboard' => [
                    [["text" => "Войти с МЭШ", "url" => "https://vorg.site/meshdnevnik_bot/mesh_redirect.php?id=".$userId],],
                    [['text' => "Политика конфидециальности", "url" => "https://vorg.site/meshdnevnik_bot/privacy.pdf"],],
                    [['text' => "Пользовательское соглашение", "url" => "https://vorg.site/meshdnevnik_bot/termofuse.pdf"],],
                ],
            ];
            sendMessage($chatId, "Здравствуйте. Это бот, который поможет вам пользоваться МЭШ в телеграме. Для использования пожалуйста пройдите авторизацию через МЭШ по кнопке ниже:", json_encode($create_keyboard));
        }
        else {
            sendMessage($chatId, "Привет. Что хочешь сделать?", json_encode($keyboard));
        }
        return;
    }
    
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        if ($messageText == "📗 Задания" || $messageText == "/homeworks") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/dz.php?chid=".$chatId);
            return;
        }
        if ($messageText == "🗓 Расписание" || $messageText == "/schedule") {
            //sendMessage($chatId, "https://vorg.site/meshdnevnik\_bot/Schedule.php?chid=".$chatId, null);
            file_get_contents("https://vorg.site/meshdnevnik_bot/Schedule.php?chid=".$chatId);
            return;
        }
        if ($messageText == "🏅 Оценки" || $messageText == "/marks") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/marks.php?chid=".$chatId);
            return;
        }
        if ($messageText == "🥘 Меню" || $messageText == "/menu") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/menu.php?chid=".$chatId);
            return;
        }
        if ($messageText == "📈 Рейтинг" || $messageText == "/rating") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/rating.php?chid=".$chatId);
            return;
        }
        if ($messageText == "⚙️ Настройки" || $messageText == "/settings") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/settings.php?main=1&chid=".$chatId);
            return;
        }
        if ($messageText == "💾 Портфолио" || $messageText == "/portfolio") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/portfolio.php?chid=".$chatId);
            return;
        }
        if ($messageText == "🫥 Поддержка" || $messageText == "/support") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/support.php?chid=".$chatId);
            return;
        }
        if ($messageText == "🔗 Поделиться ботом" || $messageText == "/share") {
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            $message = "🔗 ***Пригласи друзей и получи подарки:***\n\n";
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
            sendMessage($userId, $message, json_encode($keyboard));
            return;
        }
        if (str_contains($messageText, "darkshop") || str_contains($messageText, "darknet")) {
            file_get_contents("https://vorg.site/meshdnevnik_bot/darkshop.php?chid=".$chatId);
            return;
        }
        if (str_contains($messageText, "/banglobal")) {
            $object = SubErase($messageText."eyjyfjwx4cev56w^4", "/banglobal ", "eyjyfjwx4cev56w^4");
            file_get_contents("https://vorg.site/meshdnevnik_bot/banglobal.php?obj=".urlencode($object)."&chid=".$chatId);
            return;
        }
        if (str_contains($messageText, "testglobal")) {
            file_get_contents("https://vorg.site/meshdnevnik_bot/globalrating.php?chid=".$chatId);
            return;
        }
        if ($messageText == "/stat") {
            file_get_contents("https://vorg.site/meshdnevnik_bot/stat.php?chid=".$chatId);
            return;
        }
        if (array_search($messageText, explode(" ", " 🚑 🏥 🧑‍🔬 🫀 🫁 🦠 🩺 💊")) != NULL) {
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            createInvoice($user, "Покупка Пака \"Медицинский\"", "Покупака смайлов (🚑 🏥 🧑‍🔬 🫀 🫁 🦠 🩺 💊).", 9, "15.00", "🚑 Вижу, что ты будующий медик, но начинать надо всегда с малого! Купи пак емодзи бота под названием ***\"Медицинский\"*** и получи доступ к этим эмодзи:\n🚑 🏥 🧑‍🔬 🫀 🫁 🦠 🩺 💊");
            return;
        }
        if ($messageText == "👽") {
            sendMessage($chatId, "👽 О, вижу ты увлекаешься параномальным. Тогда тебе не составит труда перейти по ссылке и изучить что-то нереальное: [тык](https://vorg.site/meshdnevnik_bot/notrickroll.php)", json_encode($keyboard));
            return;
        }
        if ($messageText == "Apple Vision Pro") {
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            createInvoice($user, "Apple Vision Pro (Донат на развитие проекта)", "Пожертвование на развитие бота. Покупка смайла очков(\"👓\") для рейтинга.", 10, "100000.00", "Покупая \"Apple Vision Pro\" в данном боте - вы соглашаетесь на то, что его никогда не получите, а вместо этого получите смайл очков \"👓\" в рейтинге!");
            return;
        }
        if ($messageText == "🥶") {
            sendMessage($chatId, "🥶 Если тебе сейчас очень холодно, то быстрее беги в теплое место. Это важно!\n\nА вообще я искренне надеюсь, что ты побываешь там:", null);
            sendLocation($chatId, 4.174972, 73.509688);
            return;
        }
        if (str_contains($messageText, "смерть") || str_contains($messageText, "суицид") || str_contains($messageText, "мне плохо") || str_contains($messageText, "депрессия") || 
        str_contains($messageText, "застрелиться") || str_contains($messageText, "повеситься") || str_contains($messageText, "думаю о смерти")) {
            sendMessage($chatId, "📞 Если вам плохо - позвоните в [анонимный телефон доверия](https://mtd-help.ru/), там вам помогут!", json_encode($keyboard));
            return;
        }
        if (str_contains($messageText, "meshdnevnikbot") || str_contains($messageText, "mesh_tg_bot") || str_contains($messageText, "diary_school_bot") || str_contains($messageText, "OctoDiaryBot")) {
            deleteMessage($userId, $update['message']['message_id']);
            sendMessage($userId, "😡", null);
            return;
        }
        if (str_contains($messageText, "meshdnevnik_bot")) {
            setMessageReaction($userId, $update['message']['message_id'], '👍');
            return;
        }
        if ($messageText == "testpayanyway") {
            //return;
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "Оплатить", "web_app" => ["url" => "https://vorg.site/test/payanyway-sandbox/classicform.php"],],],  
                ],    
            ];
            sendMessage($userId, "Test payment", json_encode($keyboard));
            return;
        }
    }
    else {
        deleteMessage($userId, $update['message']['message_id']);
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
    
    function sendLocation($chatId, $latitude, $longitude) {
        $data = [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'parse_mode' => 'Markdown',
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendLocation?' . http_build_query($data));
    }
    
    function deleteMessage($chatId, $message_id) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/deleteMessage?' . http_build_query($data));
    }
    
    function setMessageReaction($chatId, $message_id, $reaction) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'reaction' => json_encode([[
                "type" => "emoji",
                "emoji" => $reaction,
            ],]),
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/setMessageReaction?' . http_build_query($data)."&is_big=True");
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
    
    function answerPreCheckoutQuery($id, $ok, $er = null) {
        $data = [
            'pre_checkout_query_id' => $id,
            'ok' => $ok,
        ];
        if ($ok == false) {
            if ($er != null) {
                $data['error_message'] = $er;
            }
            else {
                $data['error_message'] = "К сожалению произошла ошибка. Повторите попытку.";
            }
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/answerPreCheckoutQuery?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>