<?php
    session_start();

    require "libs/rb.php";
    
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
    
    
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    if (!R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['tg_id_d'])) && !R::findOne('meshbotparents', 'tg_id = ?', array($_SESSION['tg_id_d']))) {
        $reg_user = R::dispense('meshbotusers');
        $reg_user -> tg_id = $_SESSION['tg_id_d'];
        $reg_user -> token = $_GET['token'];
        $reg_user -> user_id = $result1['userId'];
        $reg_user -> client_id = $result2[0]['id'];
        $reg_user -> email = $result1['info']['mail'];
        $reg_user -> birthdate = $result1['info']['birthdate'];
        $reg_user -> sex = $result1['info']['gender'];
        $reg_user -> phone = "+".$result1['info']['mobile'];
        $reg_user -> snils = $result1['info']['snils'];
        $reg_user -> FIO = $result1['info']['LastName']." ".$result1['info']['FirstName']." ".$result1['info']['MiddleName'];
        $reg_user -> name = $result1['info']['FirstName'];
        $reg_user -> rating_visible = false;
        $reg_user -> profile_id = $result2[0]['person_id'];
        R::store($reg_user);
        $keyboard = [
            'keyboard' => [
                [['text' => '📗 Задания'], ['text' => '🗓 Расписание'],],
                [['text' => '🏅 Оценки'], ['text' => '📈 Рейтинг'],],
                [['text' => '🥘 Меню'], ['text' => '🫥 Поддержка'],],
                [['text' => '⚙️ Настройки'],['text' => '🔗 Поделиться']]
            ], 
            'one_time_keyboard' => false,
            'resize_keyboard' => true,
        ];
        sendMessage($_SESSION['tg_id_d'], "Здравствуйте, ".$reg_user -> name.".\n\nВижу вы авторизировались через МЭШ. Теперь вам доступен функционал бота. Сейчас немного о нем расскажу.\n
Кнопка меню \"📗 Задания\" - показывает домашние задания на сегодня, завтра и.т.д.
Кнопка меню \"🗓 Расписание\" - показывает расписание на любой из 7 дней недели на выбор.
Кнопка меню \"🏅 Оценки\" - показывает оценки по предметам и ваш средний бал.
Кнопка меню \"📈 Рейтинг\" - показывает ваш рейтинг в классе.
Кнопка меню \"⚙️ Настройки\" - показывает ваши настройки ***БОТА***.", json_encode($keyboard));
        ?>
            <script>
                window.location.href = "https://t.me/meshdnevnik_bot";
            </script>
        <?php
    }
    else {
        if (R::findOne("meshbotusers", 'tg_id = ?', array($_SESSION['tg_id_d']))) $user = R::findOne('meshbotusers', 'tg_id = ?', array($_SESSION['tg_id_d']));
        if (R::findOne("meshbotparents", 'tg_id = ?', array($_SESSION['tg_id_d']))) $user = R::findOne("meshbotparents", 'tg_id = ?', array($_SESSION['tg_id_d']));
        $user -> token = $_GET['token'];
        R::store($user);
        if (R::findOne("meshbotusers", 'tg_id = ?', array($_SESSION['tg_id_d']))) file_get_contents("https://vorg.site/meshdnevnik_bot/userupdate.php?chid=".$user -> tg_id);
        sendMessage($_SESSION['tg_id_d'], "😀 ***Вы успешно обновили авторизацию!***\n\n___Спасибо, что остаетесь с нами, возможно в будущем вам не будет требоваться проходить повторные авторизации!___\n\n🌐 ***Мы будем очень признательны, если вы подпишетесь на наши соцсети***: [Телеграм канал](https://t.me/meshdnevnik_channel) • [Twitter](https://twitter.com/mesdnevnik_bot)", null)
        
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

