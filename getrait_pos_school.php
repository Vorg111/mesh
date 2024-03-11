<?php

    $name = $_GET['name'];

    $result = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top20.txt");
    $result20 = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top70.txt");
    $result170 = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top170.txt");
    $result220 = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top220.txt");
    $result300 = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top300.txt");
    $result400 = file_get_contents("https://vorg.site/meshdnevnik_bot/schools_rait/top400.txt");
    if (str_contains($result, $name)) echo "1";
    elseif (str_contains($result20, $name)) echo "21";
    elseif (str_contains($result170, $name)) echo "71";
    elseif (str_contains($result220, $name)) echo "171";
    elseif (str_contains($result300, $name)) echo "221";
    elseif (str_contains($result400, $name)) echo "301";
    else echo "401";
?>