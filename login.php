<?php
    session_start();

    require "libs/rb.php";
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
    
    $token = "6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8";
    
    $url = "https://school.mos.ru/v3/userinfo";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer ".$_GET['token'],
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result1 = json_decode(curl_exec($ch), true);
    
    
    $url = "https://dnevnik.mos.ru/core/api/student_profiles/";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Auth-Token:".$_GET['token'],
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result2 = json_decode(curl_exec($ch), true);
    
    
        
    if (!R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['tg_id'])) && !R::findOne('meshbotparents', 'tg_id = ?', array($_SESSION['tg_id']))) {
        if ($result1['roles'][0]['title'] == 'Родитель') {
            
            $url = "https://dnevnik.mos.ru/core/api/student_profiles";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$_GET['token'],
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
            
            $reg_user = R::dispense('meshbotparents');
            $reg_user -> tg_id = $_SESSION['tg_id'];
            $reg_user -> token = $_GET['token'];
            $reg_user -> user_id = $result1['userId'];
            $reg_user -> email = $result1['info']['mail'];
            $reg_user -> birthdate = $result1['info']['birthdate'];
            $reg_user -> sex = $result1['info']['gender'];
            if (isset($result1['info']['mobile'])) $reg_user -> phone = "+".$result1['info']['mobile'];
            $reg_user -> snils = $result1['info']['snils'];
            $reg_user -> FIO = $result1['info']['LastName']." ".$result1['info']['FirstName']." ".$result1['info']['MiddleName'];
            $reg_user -> name = $result1['info']['FirstName'];
            $reg_user -> active_children = $result2[0]['id'];
            $reg_user -> active_profid = $result2[0]['person_id'];
            $reg_user -> active_name = $result2[0]['user_name'];
            $reg_user -> marksnotifications = true;
            R::store($reg_user);
            if (isset($_SESSION['ref_id'])) {
                $user_ref = R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['ref_id']));
                if (isset($user_ref)) {
                    $user_ref -> refs++;
                    if ($user_ref -> refs == 5) {
                        $user_ref -> added_emoji .= " 🚠 🚃 🚇 🚀️";
                    }
                    R::store($user_ref);
                }
            }
            $keyboard = [
                'keyboard' => [
                    [['text' => '📗 Задания'], ['text' => '🗓 Расписание'],],
                    [['text' => '🏅 Оценки'], ['text' => '📈 Рейтинг'],],
                    [['text' => '🥘 Меню'], ['text' => '🫥 Поддержка'],],
                    [['text' => '💾 Портфолио'], ['text' => '⚙️ Настройки'],],
                    [['text' => '🆗 Посещаемость'], ['text' => '🔗 Поделиться'],],
                ], 
                'one_time_keyboard' => false,
                'resize_keyboard' => true,
            ];
            sendMessage($_SESSION['tg_id'], "Здравствуйте, ".$reg_user -> name.".\n\nВижу вы авторизовались через МЭШ. Теперь вам доступен функционал бота. Сейчас немного о нем расскажу.\n
Кнопка меню \"📗 Задания\" - показывает домашние задания ребенка на сегодня, завтра и.т.д.
Кнопка меню \"🗓 Расписание\" - показывает расписание ребенка на любой из 7 дней недели на выбор.
Кнопка меню \"🏅 Оценки\" - показывает оценки по предметам и средний бал вашего ребенка.
Кнопка меню \"📈 Рейтинг\" - показывает рейтинг в классе.
Кнопка меню \"⚙️ Настройки\" - показывает ваши настройки ***БОТА***.", json_encode($keyboard));
            ?>
                <script>
                    window.location.href = "https://t.me/meshdnevnik_bot";
                </script>
            <?php
            return;
        }
        $str = "🐶 🐱 🐻 🐨 🐯 🦁 🐮 🐣 🦉 🐺 🦄 🐴 🐢 🦂 🐙 🐠 🐡 🐘 🦃 🦜 🦧 🦈 🫎 🍎 🍐 🍊 🍋 🍌 🍉 🍇 🍒 🍑 🥭 🍍 🥝 🍅";
        $smiles = explode(" ", $str);
        
        $reg_user = R::dispense('meshbotusers');
        $reg_user -> tg_id = $_SESSION['tg_id'];
        $reg_user -> token = $_GET['token'];
        $reg_user -> user_id = $result1['userId'];
        $reg_user -> client_id = $result2[0]['id'];
        $reg_user -> email = $result1['info']['mail'];
        $reg_user -> birthdate = $result1['info']['birthdate'];
        $reg_user -> sex = $result1['info']['gender'];
        if (isset($result1['info']['mobile'])) $reg_user -> phone = "+".$result1['info']['mobile'];
        $reg_user -> snils = $result1['info']['snils'];
        $reg_user -> FIO = $result1['info']['LastName']." ".$result1['info']['FirstName']." ".$result1['info']['MiddleName'];
        $reg_user -> name = $result1['info']['FirstName'];
        $reg_user -> rating_visible = false;
        $reg_user -> profile_id = $result2[0]['person_id'];
        $reg_user -> rating_smile = $smiles[rand(0, sizeof($smiles) - 1)];
        $reg_user -> added_emoji = $reg_user -> rating_smile;
        $reg_user -> base_emoji = $reg_user -> rating_smile;
        $reg_user -> refs = 0;
        $reg_user -> marksnotifications = true;
        $reg_user -> dailynotifications = true;
        R::store($reg_user);
        $reg_user -> school = "".file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?chid=".$reg_user -> tg_id);
        $reg_user -> school_ids = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?id=1&chid=".$reg_user -> tg_id);
        $reg_user -> school_data = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?data=1&chid=".$reg_user -> tg_id);
        $reg_user -> st_class = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?class=1&chid=".$reg_user -> tg_id);
        R::store($reg_user);
        if (isset($_SESSION['ref_id'])) {
            $user_ref = R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['ref_id']));
            if (isset($user_ref)) {
                $user_ref -> refs++;
                if ($user_ref -> refs == 5) {
                    $user_ref -> added_emoji .= " 🚠 🚃 🚇 🚀️";
                }
                R::store($user_ref);
            }
        }
        if (isset($_SESSION['promo_id'])) {
            $promo = R::findOne('meshbotpromo', 'promo_id = ?', array($_SESSION['promo_id']));
            if (isset($promo)) {
                $promo -> reg_count++;
                R::store($promo);
            }
        }
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
        sendMessage($_SESSION['tg_id'], "Здравствуйте, ".$reg_user -> name.".\n\nВижу вы авторизовались через МЭШ. Теперь вам доступен функционал бота. Сейчас немного о нем расскажу.\n
Кнопка меню \"📗 Задания\" - показывает домашние задания на сегодня, завтра и.т.д.
Кнопка меню \"🗓 Расписание\" - показывает расписание на любой из 7 дней недели на выбор.
Кнопка меню \"🏅 Оценки\" - показывает оценки по предметам и ваш средний бал.
Кнопка меню \"📈 Рейтинг\" - показывает ваш рейтинг в классе.
Кнопка меню \"⚙️ Настройки\" - показывает ваши настройки ***БОТА***.", json_encode($keyboard));
        file_get_contents("https://vorg.site/meshdnevnik_bot/userupdate.php?chid=".$user -> tg_id);
        ?>
            <script>
                window.location.href = "https://t.me/meshdnevnik_bot";
            </script>
        <?php
    }
    else {
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
        sendMessage($_SESSION['tg_id'], "Здравствуйте, ".R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['tg_id'])) -> name.".\n\nВижу, что вы уже некоторое время с нами, но раз вы повторно пытались войти через МЭШ - скорее всего у вас возникли проблемы. Сейчас расскажу краткую инструкцию.\n
Кнопка меню \"📗 Задания\" - показывает домашние задания на сегодня, завтра и.т.д.
Кнопка меню \"🗓 Расписание\" - показывает расписание на любой из 7 дней недели на выбор.
Кнопка меню \"🏅 Оценки\" - показывает оценки по предметам и ваш средний бал.
Кнопка меню \"📈 Рейтинг\" - показывает ваш рейтинг в классе.
Кнопка меню \"⚙️ Настройки\" - показывает ваши настройки ***БОТА***.\n\nЕсли это не решает вашу проблему, то напишите в поддержку - `meshbot@vvvorg.com`!", json_encode($keyboard));
        ?>
            <script>
                window.location.href = "https://t.me/meshdnevnik_bot";
            </script>
        <?php
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
?>

