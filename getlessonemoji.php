<?php
    $i['subject_name'] = $_GET['n'];
    $message = "";
    if ($i['subject_name'] == "Русский язык") {
        $message .= "✒️ ";
    }
    elseif ($i['subject_name'] == "Математика" || $i['subject_name'] == "Алгебра") {
        $message .= "➕️ ";
    }
    elseif ($i['subject_name'] == "Геометрия") {
        $message .= "📐 ️";
    }
    elseif ($i['subject_name'] == "Информатика") {
        $message .= "👨‍💻 ️";
    }
    elseif ($i['subject_name'] == "История России. Всеобщая история") {
        $message .= "🎞 ";
    }
    elseif (str_contains($i['subject_name'], "программирован") || str_contains($i['subject_name'], "Программирован")) {
        $message .= "💻 ️";
    }
    elseif (str_contains($i['subject_name'], "Робототехника")) {
        $message .= "🤖 ️";
    }
    elseif ($i['subject_name'] == "Музыка") {
        $message .= "🎼 ️";
    }
    elseif ($i['subject_name'] == "Литература") {
        $message .= "📚 ️";
    }
    elseif ($i['subject_name'] == "География") {
        $message .= "🌏 ️";
    }
    elseif ($i['subject_name'] == "Физическая культура") {
        $message .= "🧗🏿 ️";
    }
    elseif ($i['subject_name'] == "Обществознание") {
        $message .= "🎭 ️";
    }
    elseif ($i['subject_name'] == "История") {
        $message .= "🎞 ️";
    }
    elseif ($i['subject_name'] == "Физика") {
        $message .= "⚡️ ️";
    }
    elseif ($i['subject_name'] == "Химия") {
        $message .= "🧪️ ️";
    }
    elseif ($i['subject_name'] == "Биология") {
        $message .= "🧬️ ️";
    }
    elseif ($i['subject_name'] == "Экономика") {
        $message .= "💲️ ️";
    }
    elseif ($i['subject_name'] == "Английский язык") {
        $message .= "🇬🇧️ ️";
    }
    elseif ($i['subject_name'] == "Французский язык") {
        $message .= "🇫🇷️ ️";
    }
    elseif ($i['subject_name'] == "Немецкий язык") {
        $message .= "🇩🇪️ ️";
    }
    elseif ($i['subject_name'] == "Украинский язык") {
        $message .= "🇺🇦️ ️";
    }
    elseif ($i['subject_name'] == "Китайский язык") {
        $message .= "🇨🇳️ ️";
    }
    elseif ($i['subject_name'] == "Испанский язык") {
        $message .= "🇪🇸️ ️";
    }
    elseif ($i['subject_name'] == "Итальянский язык") {
        $message .= "🇮🇹️ ️";
    }
    elseif ($i['subject_name'] == "Японский язык") {
        $message .= "🇯🇵️ ️";
    }
    elseif ($i['subject_name'] == "Идиш") {
        $message .= "🇮🇱️ ️";
    }
    elseif ($i['subject_name'] == "Иврит") {
        $message .= "🇮🇱️ ️";
    }
    elseif ($i['subject_name'] == "Турецкий язык") {
        $message .= "🇹🇷️ ️";
    }
    elseif (str_contains($i['subject_name'], "язык")) {
        $message .= "🌐️ ️";
    }
    elseif ($i['subject_name'] == "Основы безопасности жизнедеятельности") {
        $message .= "🚧️ ️";
    }
    elseif ($i['subject_name'] == "Вероятность и статистика") {
        $message .= "🪙️ ️";
    }
    elseif ($i['subject_name'] == "Риторика") {
        $message .= "🤔️ ️";
    }
    elseif ($i['subject_name'] == "Черчение") {
        $message .= "📝️ ️";
    }
    elseif ($i['subject_name'] == "Практикум по математике") {
        $message = "🔢 ";
    }
    elseif ($i['subject_name'] == "Технология") {
        $message .= "⚙️️ ️";
    }
    elseif ($i['subject_name'] == "Изобразительное искусство") {
        $message .= "🎨️ ️";
    }
    else {
        $message .= "❕️ ️";
    }
    echo $message;
?>