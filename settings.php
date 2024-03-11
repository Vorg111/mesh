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
    
    function createInvoice($user, $name, $description, $id, $price) {
        $check = [
           0 => "🖱", 1 => "💎", 2 => "🛟", 3 => "👑", 4 => "🤎",
           5 => "🐼", 6 => "🥥", 7 => "🐊", 8 => "🍪", 9 => "🫁",
        ];
        if (array_search($check[$id], explode(" ", ($user -> added_emoji))) != NULL) {
            $keyboard = [
                'inline_keyboard' => [
                    [["text" => "← Назад", "callback_data" => "st.shop."]],
                ],
            ];
            editMessage($user -> tg_id, $_GET['mid'], "💶 Либо ты ошибся, либо ты очень хочешь дать мне деньги.\n\n***Ты уже купил данный товар!***", json_encode($keyboard));
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
            deleteMessage($user -> tg_id, $_GET['mid']);
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
                [["text" => "← Назад", "callback_data" => "st.shop."]],
            ],
        ];
        
        editMessage($user -> tg_id, $_GET['mid'], "💥 $name\n🔹 $desc\n\n💰 Цена: ***$price"."₽***", json_encode($keyboard));
    }
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        
        if (isset($_GET['goodmorning_notific'])) {
            $user -> goodmorning_notific = !($user -> goodmorning_notific);
            R::store($user);
            $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\n📣 Натройте уведомления \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $_GET['notifications'] = 1;
        }
        if (isset($_GET['marksnotific'])) {
            $user -> marksnotifications = !($user -> marksnotifications);
            R::store($user);
            $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\n📣 Натройте уведомления \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $_GET['notifications'] = 1;
        }
        if (isset($_GET['dailynotific'])) {
            $user -> dailynotifications = !($user -> dailynotifications);
            R::store($user);
            $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\n📣 Натройте уведомления \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $_GET['notifications'] = 1;
        }
        if (isset($_GET['notifications'])) {
            if (!isset($message)) $message = "📣 Натройте уведомления \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $keyboard = [
              "inline_keyboard" => [
                ],  
            ];
            if (!$user -> marksnotifications) {
                array_push($keyboard['inline_keyboard'], [["text" => "🔔 Присылать уведомления об оценках", "callback_data" => "st.marksnotific."]]);
                $message .= "Кнопка ***\"🔔 Присылать уведомления об оценках\"*** - позволяет вам получать уведомления о новых оценках.\n\n";
            }
            else {
                array_push($keyboard['inline_keyboard'], [["text" => "🔕 Не присылать уведомления об оценках", "callback_data" => "st.marksnotific."]]);
                $message .= "Кнопка ***\"🔕 Не присылать уведомления об оценках\"*** - вы перестанете получать уведомления о новых оценках.\n\n";
            }
            
            if (!$user -> dailynotifications) {
                array_push($keyboard['inline_keyboard'], [["text" => "🔔 Присылать итоги дня", "callback_data" => "st.dailynotific."]]);
                $message .= "Кнопка ***\"🔔 Присылать итоги дня\"*** - позволяет вам получать итоги дня (сегодняшние оценк и расписание на завтра).\n\n";
            }
            else {
                array_push($keyboard['inline_keyboard'], [["text" => "🔕 Не присылать итоги дня", "callback_data" => "st.dailynotific."]]);
                $message .= "Кнопка ***\"🔕 Не присылать итоги дня\"*** - вы перестанете получать итоги дня (сегодняшние оценк и расписание на завтра).\n\n";
            }
            if (!$user -> goodmorning_notific) {
                array_push($keyboard['inline_keyboard'], [["text" => "🔔 Присылать утреннее уведомление", "callback_data" => "st.goodmorning_notific."]]);
                $message .= "Кнопка ***\"🔔 Присылать утреннее уведомление\"*** - позволяет вам получать утром дз, изменения в рейтинге за вчера и первый урок этого дня.\n\n";
            }
            else {
                array_push($keyboard['inline_keyboard'], [["text" => "🔕 Не присылать утреннее уведомление", "callback_data" => "st.goodmorning_notific."]]);
                $message .= "Кнопка ***\"🔕 Не присылать утреннее уведомление\"*** - вы перестанете получать утром дз, изменения в рейтинге за вчера и первый урок этого дня.\n\n";
            }
            array_push($keyboard['inline_keyboard'], [["text" => "← Назад", "callback_data" => "st.main."]]);
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        if (isset($_GET['rating'])) {
            $message = "📈 Натройте рейтинг \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $keyboard = [
              "inline_keyboard" => [
                ],  
            ];
            if (!$user -> rating_visible) {
                array_push($keyboard['inline_keyboard'], [["text" => "🔐 Показывать рейтинг", "callback_data" => "st.raitonoff."]]);
                $message .= "Кнопка ***\"🔐 Показывать рейтинг\"*** - показывает ваше имя в рейтинге другим пользователям бота.\n\n";
            }
            else {
                array_push($keyboard['inline_keyboard'], [["text" => "🔏 Не показывать рейтинг", "callback_data" => "st.raitonoff."]]);
                $message .= "Кнопка ***\"🔏 Не показывать рейтинг\"*** - перестает показывает ваше имя в рейтинге другим пользователям бота.\n\n";
            }
            array_push($keyboard['inline_keyboard'], [["text" => "😀 Изменить эмодзи рейтинга", "callback_data" => "st.emojirating."]]);
            array_push($keyboard['inline_keyboard'], [["text" => "← Назад", "callback_data" => "st.main."]]);
            $message .= "Кнопка ***\"😀 Изменить эмодзи рейтинга\"*** - открывает меню для выбора эмодзи, который показывается рядом с вами в рейтинге класса.\n\n";
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        if (isset($_GET['raitonoff'])) {
            $user -> rating_visible = !($user -> rating_visible);
            R::store($user);
            $message = "___(Ваши данные изменены ".date("d.m.y H:i:s").")___\n\n📈 Натройте рейтинг \n___(настройки отражаются исключительно на функционал/пользователей бота)___\n\n";
            $keyboard = [
              "inline_keyboard" => [
                ],  
            ];
            if (!$user -> rating_visible) {
                array_push($keyboard['inline_keyboard'], [["text" => "🔐 Показывать рейтинг", "callback_data" => "st.raitonoff."]]);
                $message .= "Кнопка ***\"🔐 Показывать рейтинг\"*** - показывает ваше имя в рейтинге другим пользователям бота.\n\n";
            }
            else {
                array_push($keyboard['inline_keyboard'], [["text" => "🔏 Не показывать рейтинг", "callback_data" => "st.raitonoff."]]);
                $message .= "Кнопка ***\"🔏 Не показывать рейтинг\"*** - перестает показывает ваше имя в рейтинге другим пользователям бота.\n\n";
            }
            array_push($keyboard['inline_keyboard'], [["text" => "😀 Изменить эмодзи рейтинга", "callback_data" => "st.emojirating."]]);
            array_push($keyboard['inline_keyboard'], [["text" => "← Назад", "callback_data" => "st.main."]]);
            $message .= "Кнопка ***\"😀 Изменить эмодзи рейтинга\"*** - открывает меню для выбора эмодзи, который показывается рядом с вами в рейтинге класса.\n\n";
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        if (isset($_GET['emojiratingg'])) {
            $fromrait = true;
            $_GET['emojirating'] = 1;
        }
        if (isset($_GET['emojirating'])) {
            $message = "😁 Если вы откроете полный рейтинг, то увидете слева от каждого места - смайлик! Вы можете его изменить для этого ответьте на это сообщение любым из ниже написанных:\n\n";
            $message .= $user -> added_emoji."\n\n";
            $message .= "***Ваш нынешний смайл: ***".$user -> rating_smile."\n\n";
            $message .= "👑 Вы так же можете получить дополнительные смайлики, для этого нажмите на кнопку ***\"🔥 Привелегированные\"*** или ***\"🛍 Магазин\"*** ниже!";
            $keyboard = [
              "inline_keyboard" => [
                  [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                  [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                  [["text" => "← Назад", "callback_data" => "st.rating."]],
                ],  
            ];
            if (isset($fromrait)) {
            $keyboard = [
              "inline_keyboard" => [
                  [["text" => "🔥 Привелегированные", "callback_data" => "st.vipsmiles."]],
                  [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                  [["text" => "⚙️ Другие настройки", "callback_data" => "st.main."]],
                  [["text" => "← Обратно в рейтинг", "callback_data" => "rt.short."]],
                ],  
            ];
            }
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            
            return;
        }
        if (isset($_GET['vipchecks'])) {
            $str = " ";
            $str .= $user -> added_emoji;
            
            $smiles = explode(" ", $str);
            
            
            $url = "https://dnevnik.mos.ru/core/api/academic_years?only_current_year=true";
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $year = json_decode(curl_exec($ch), true)[0]['id'];
        
        
        
            $url = "https://dnevnik.mos.ru/reports/api/progress/json?academic_year_id=$year&student_profile_id=".$user -> client_id;
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
            
            if (str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                return;
            }
            
            
            
            
            if (array_search("😳", $smiles) == NULL) {
                if (getChatMember($userId)) {
                    $user -> added_emoji .= " 😀 🙃 😳 😉";
                }
            }
            if (array_search("🥌", $smiles) == NULL) {
                foreach ($result2 as $i) {
                    if ($i['subject_name'] == "Физическая культура") {
                        if (isset($i['periods'][1]['avg_five'])) {
                            if (isset($i['periods'][2]['avg_five'])) {
                                if ($i['periods'][2]['avg_five'] == "5.00") $user -> added_emoji .= " ⚽️ ⚾️ 🥌 ⛳️";
                            }
                            else {
                                if ($i['periods'][1]['avg_five'] == "5.00") $user -> added_emoji .= " ⚽️ ⚾️ 🥌 ⛳️";
                            }
                        }
                        else {
                            if ($i['periods'][0]['avg_five'] == "5.00") $user -> added_emoji .= " ⚽️ ⚾️ 🥌 ⛳️";
                        }
                        break;
                    }
                }
            }
            if (array_search("🔥", $smiles) == NULL) {
                foreach ($result2 as $i) {
                    if ($i['subject_name'] == "Физика") {
                        if (isset($i['periods'][1]['avg_five'])) {
                            if (isset($i['periods'][2]['avg_five'])) {
                                if ($i['periods'][2]['avg_five'] == "5.00") $user -> added_emoji .= " 🌧 🔥 🌍 ☄️️";
                            }
                            else {
                                if ($i['periods'][1]['avg_five'] == "5.00") $user -> added_emoji .= " 🌧 🔥 🌍 ☄️️";
                            }
                        }
                        else {
                            if ($i['periods'][0]['avg_five'] == "5.00") $user -> added_emoji .= " 🌧 🔥 🌍 ☄️️";
                        }
                        break;
                    }
                }
            }
            
            R::store($user);
            
            $_GET['vipsmiles'] = 1;
        }
        if (isset($_GET['vipsmiles'])) {
            $message = "🔥 ***О, вижу ты фанат Fortnite!***\n\nТы попал в обитель самых бесполезных скинов в твоей жизни, но раз уж ты захотел ***красоваться особенными смайликами*** перед однокласниками, то можешь выполнять задания и открывать паки смайлов:\n\n";
            
            if (isset($_GET['vipchecks'])) {
                $message = "___(Проведена проверка данных и возможно внесены изменения в ваши данные ".date("d.m.y H:i:s").")___\n\n".$message;
            }
            $keyboard = [
              "inline_keyboard" => [
                  [["text" => "🔎 Проверить", "callback_data" => "st.vipchecks."]],
                  [["text" => "🛍 Магазин", "callback_data" => "st.shop."]],
                  [["text" => "← Назад", "callback_data" => "st.emojirating."]],
                ],  
            ];
            
            
            
            $str = " ";
            $str .= $user -> added_emoji;
            
            $smiles = explode(" ", $str);
            
            if (array_search("🚠", $smiles) != NULL) {
                $message .= "🟢 ***Пак \"Транспорт\" (🚠 🚃 🚇 🚀️)*** - ***ВЫПОЛНЕНО!***\n\n";
            }
            else {
                $message .= "🔹 ***Пак \"Транспорт\" (🚠 🚃 🚇 🚀️)*** - Для получения этого пака надо пригласить 5 друзей в бота по своей реферальной ссылке!\n\n";
            }
            
            if (array_search("😳", $smiles) != NULL) {
                $message .= "🟢 ***Пак \"Классик\" (😀 🙃 😳 😉)*** - ***ВЫПОЛНЕНО!***\n\n";
            }
            else {
                $message .= "🔹 ***Пак \"Классик\" (😀 🙃 😳 😉)*** - Для получения этого пака надо подписаться на наш [телеграм канал](https://t.me/meshdnevnik_channel)\n\n";
            }
            
            if (array_search("👀", $smiles) != NULL) {
                $message .= "🟢 ***Пак \"Очки\" (👓 🤿 🥽 👀 🐲)*** - ***ВЫПОЛНЕНО!***\n\n";
            }
            else {
                //$message .= "🔹 ***Пак \"Очки\" (👓 🤿 🥽 👀 🐲)*** - Для получения этого пака надо подписаться на наш [Twitter](https://twitter.com/mesdnevnik_bot) и подключить его в разделе настроек ***\"🌐 Подключенные сервисы\"***\n\n";
            }
            
            if (array_search("🥌", $smiles) != NULL) {
                $message .= "🟢 ***Пак \"Спорт\" (⚽️ ⚾️ 🥌 ⛳️)*** - ***ВЫПОЛНЕНО!***\n\n";
            }
            else {
                $message .= "🔹 ***Пак \"Спорт\" (⚽️ ⚾️ 🥌 ⛳️)*** - Для получения этого пака надо иметь средний балл 5.00 по Физической Культуре и иметь не меньше 7 оценок\n\n";
            }
            
            if (array_search("🔥", $smiles) != NULL) {
                $message .= "🟢 ***Пак \"Природа\" (🌧 🔥 🌍 ☄️)*** - ***ВЫПОЛНЕНО!***\n\n";
            }
            else {
                $message .= "🔹 ***Пак \"Природа\" (🌧 🔥 🌍 ☄️)*** - Для получения этого пака надо средний балл 5.00 по Физике и иметь не меньше 5 оценок\n\n";
            }
            
            $message .= "💰 Так же вы можете приобрести другие смайлы за деньги в разделе настроек ***\"🛍 Магазин\"***!\n\n";
            
            $message .= "✅ Для получения смайлов - нажмите на кнопку ***\"🔎 Проверить\"*** и если в данный момент требования для любого из паков вполняется, то вы получите его навсегда!";
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            
            return;
        }
        if (isset($_GET['shop'])) {
            $animals = explode(" ", "🐶 🐱 🐭 🐹 🐰 🦊 🐻 🐼 🐻 🐨 🐯 🦁 🐮 🐷 🐸 🐵 🐔 🐧 🐣 🦉 🐺 🦄 🐴 🐢 🦂 🐘 🦃 🦜 🦚 🫎 🦧 🐌 🦝");
            unset($animals[array_search($user -> base_emoji, $animals)]);
            
            $fruits = explode(" ", "🍎 🍐 🍊 🍋 🍌 🍉 🍇 🍓 🫐 🍈 🍒 🍑 🥭 🍍 🥥 🥝 🍅");
            unset($fruits[array_search($user -> base_emoji, $fruits)]);
            
            $waterworld = explode(" ", "🐙 🐠 🐡 🐳 🪼 🦈 🐋 🦑 🦐 🦀 🐊");
            unset($waterworld[array_search($user -> base_emoji, $waterworld)]);
            
            $message = "***🛍 Магазин***\n\n";
            
            $message .= "✔️ Для выбора товара - выберите его номер в меню ниже!\n\n";
            
            $message .= "🔹 *1*. *20₽*  -  Пак \"Туризм\" - Вы сможете использовать смайлы из этого пака (🛟 🚢 🎢 🎡 🏟 ⛱ 🗽 ⛺ ️🗺), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *2*. *200₽*  -  Пак \"Королевский\" - Вы сможете использовать смайлы из этого пака (👑 🫅 🤴🏿 🧜🏻‍♂️ 🪤 💸), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *3*. *20₽*  -  Пак \"Техника\" - Вы сможете использовать смайлы из этого пака (⌚️ 📱 🖥 🖱 💽 📀 🎞 ☎ ️ 📺 📠 📽 ⏰), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *4*. *15₽*  -  Пак \"Сердца\" - Вы сможете использовать смайлы из этого пака (🩷 ❤️ 🧡 💛 💚 🩵 💙 💜 🖤 🩶 🤍 🤎 ❤️‍🔥 💕), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *5*. *110₽*  -  Пак \"Животные\" - Вы сможете использовать смайлы из этого пака (".implode(" ", $animals)."), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *6*. *70₽*  -  Пак \"Фрукты\" - Вы сможете использовать смайлы из этого пака (".implode(" ", $fruits)."), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *7*. *25₽*  -  Пак \"Морские жители\" - Вы сможете использовать смайлы из этого пака (".implode(" ", $waterworld)."), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "🔹 *8*. *25₽*  -  Пак \"Еда\" - Вы сможете использовать смайлы из этого пака (🍔 🥞 🧀 🥔 🍞 🍪 🎂 🍰 🍕 🍖 🍳 🥐 🍯 🍭), как смайлик рейтинга, который устанавливается исключительно для нашего бота.\n\n";
            
            $message .= "‼️ ___ВНИМАНИЕ! По мнению администрации бота - товары в даннном магазине не являются обязательными для вашей жизни. Так что, пожалуйста, осуществляйте покупку только в случае, если вы не будете тратить на это важную для вас сумму. Спасибо за то, что поддерживаете нас!___\n\n";
            
            $message .= "‼️ ___Для возврата средств вы можете обратиться в поддержку в течение 5 дней после покупки. Возврат будет осуществлен максимум на 80% от покупки.___\n\n";
            
            $keyboard = [
              "inline_keyboard" => [
                  [["text" => "1", "callback_data" => "st.buyprocess1."], ["text" => "2", "callback_data" => "st.buyprocess2."], ["text" => "3", "callback_data" => "st.buyprocess3."], ["text" => "4", "callback_data" => "st.buyprocess4."],],
                  [["text" => "5", "callback_data" => "st.buyprocess5."], ["text" => "6", "callback_data" => "st.buyprocess6."], ["text" => "7", "callback_data" => "st.buyprocess7."], ["text" => "8", "callback_data" => "st.buyprocess8."],],
                  [["text" => "← Назад", "callback_data" => "st.main."]],
                ],  
            ];
            
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            
            return;
        }
        
        if (isset($_GET['buyprocess1'])) {
            createInvoice($user, "Покупка Пака \"Туризм\"", "Покупка смайлов из этого пака (🛟 🚢 🎢 🎡 🏟 ⛱ 🗽 ⛺ ️🗺).", 2,20.00);
            return;
        }
        if (isset($_GET['buyprocess2'])) {
            createInvoice($user, "Покупка Пака \"Королевский\"", "Покупка смайлов из этого пака (👑 🫅 🤴🏿 🧜🏻‍♂️ 🪤 💸).", 3, 200.00);
            return;
        }
        if (isset($_GET['buyprocess3'])) {
            createInvoice($user, "Покупка Пака \"Техника\"", "Покупка смайлов из этого пака (⌚️ 📱 🖥 🖱 💽 📀 🎞 ☎ ️ 📺 📠 📽).", 0, 20.00);
            return;
        }
        if (isset($_GET['buyprocess4'])) {
            createInvoice($user, "Покупка Пака \"Сердца\"", "Покупка смайлов из этого пака (🩷 ❤️ 🧡 💛 💚 🩵 💙 💜 🖤 🩶 🤍 🤎 ❤️‍🔥 💕).", 4, 15.00);
            return;
        }
        if (isset($_GET['buyprocess5'])) {
            createInvoice($user, "Покупка Пака \"Животные\"", "Покупка смайлов из пака \"Животные\".", 5, 110.00);
            return;
        }
        if (isset($_GET['buyprocess6'])) {
            createInvoice($user, "Покупка Пака \"Фрукты\"", "Покупка смайлов из пака \"Фрукты\".", 6, 70.00);
            return;
        }
        if (isset($_GET['buyprocess7'])) {
            $waterworld = explode(" ", "🐙 🐠 🐡 🐳 🪼 🦈 🐋 🦑 🦐 🦀 🐊");
            unset($waterworld[array_search($user -> base_emoji, $waterworld)]);
            createInvoice($user, "Покупка Пака \"Морские жители\"", "Покупка смайлов из этого пака (".implode(" ", $waterworld).").", 7, 25.00);
            return;
        }
        if (isset($_GET['buyprocess8'])) {
            createInvoice($user, "Покупка Пака \"Еда\"", "Покупка смайлов из этого пака (🍔 🥞 🧀 🥔 🍞 🍪 🎂 🍰 🍕 🍖 🍳 🥐 🍯 🍭).", 8, 25.00);
            return;
        }
        
        if (isset($_GET['main'])) {
            $message = "⚙️ Добро пожаловать в настройки:

💾 Ваши данные:\nИмя: ***".$user -> fio."***\n";
            if (isset($user -> phone)) {
                $message .= "Номер телефона: ***".$user -> phone."***\n";
            }
            if (isset($user -> email)) {
                $message .= "Email: ***".$user -> email."***\n";
            }
            if (isset($user -> snils)) {
                $message .= "Снилс: ***".$user -> snils."***\n";
            }
            if (isset($user -> twitter)) {
                $message .= "Twitter: ***".$user -> twitter."***\n";
            }
            $message .= "Смайл рейтинга: ***".$user -> rating_smile."***\n";
            $message .= "\n";
            if ($user -> in_global) {
                $message .= "🌍 Глобальный рейтинг:\n";
                $message .= "Ник: ***".($user -> name_global);
                $message .= "\n***Балл: ***".log($user -> global_ball)."***\n";
                $message .= "\n";
            }
            
            if (isset($user -> school)) {
                $message .= "🏫 Школа:\n";
                $message .= "Название: ***".$user -> school."***\n";
                if (isset($user -> school_data)) {
                    $school_data = json_decode($user -> school_data, true);
                    if (isset($school_data['phone'])) {
                        $message .= "Номер телефона: ***+7 ".$school_data['phone']."***\n";
                    }
                    if (isset($school_data['email'])) {
                        $message .= "Email: ***".$school_data['email']."***\n";
                    }
                    if (isset($school_data['website_link'])) {
                        $message .= "Сайт: ***".$school_data['website_link']."***\n";
                    }
                }
                if ($user -> st_class) {
                    $message .= "Класс: ***".$user -> st_class."***\n";
                }
                $message .= "Кол-во юзеров бота из школы: ***".R::count("meshbotusers", "school_ids = ?", array($user -> school_ids))."***\n";
            }
            
            $keyboard = [
              "inline_keyboard" => [
                  //[["text" => "🔏 Отображение рейтинга", "callback_data" => "st.rating"]],
                  [["text" => "📈 Настроки рейтинга", "callback_data" => "st.rating."]],
                ],  
            ];
            
            //array_push($keyboard['inline_keyboard'], [["text" => "🌐 Подключенные сервисы", "callback_data" => "st.connect."]]);
            array_push($keyboard['inline_keyboard'], [["text" => "🛍 Магазин", "callback_data" => "st.shop."]]);
            array_push($keyboard['inline_keyboard'], [["text" => "📣 Уведомления", "callback_data" => "st.notifications."]]);
            //array_push($keyboard['inline_keyboard'], [["text" => "❌ Отвязать МЭШ", "callback_data" => "st.delete."]]);
            
            if (isset($_GET['mid'])) {
                editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                return;
            }
            
            sendMessage($userId, $message, json_encode($keyboard));
            
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
    
    
    function sendInvoice($chatId, $title, $description, $amount) {
        $data = [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $chatId.".".$title,
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
    
    
    function getChatMember($userId) {
        $data = [
            'chat_id' => "@meshdnevnik_channel",
            'user_id' => $userId,
        ];
        return ((json_decode(file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/getChatMember?' . http_build_query($data)), true))['result']['status'] != "left" &&
        (json_decode(file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/getChatMember?' . http_build_query($data)), true))['result']['status'] != "kicked");
    }
    
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения 
?>   