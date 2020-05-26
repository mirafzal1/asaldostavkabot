<?php

require_once 'db_connect.php';

include 'Telegram.php';

$telegram = new Telegram('1238584012:AAHjfJjzfYl_euPa8qCj1PKQzDqfwvX7Nio');

$filePath = 'users/step.txt';

$data = $telegram->getData();

$message = $data['message'];

//sendMessageJson();

$text = $message['text'];
$chat_id = $message['chat']['id'];

$orderTypes = ["1kg - 50 000 so'm", "1.5kg (1L) - 75 000 so'm", "4.5kg (3L) - 220 000 so'm", "7.5kg (5L) - 370 000 so'm"];

switch ($text) {
    case "/start":
        showStart();
        break;
    case "🍯 Batafsil ma'lumot":
        showAbout();
        break;
    case "🍯 Buyurtma berish":
        showOrder();
        break;
    case "✈️ Yetkazib berish ✈":
        showInputLocation();
        break;
    case "🔙 Orqaga":
        switch (file_get_contents($filePath)) {
            case 'start':
                break;
            case 'order':
                showStart();
                break;

        }
        break;
    default:
        if (in_array($text, $orderTypes)) {
            file_put_contents('users/massa.txt', $text);
            showInputContact();
        } else {
            switch (file_get_contents('users/step.txt')) {
                case 'phone':
                    if ($message['contact']['phone_number'] != "") {
                        file_put_contents('users/phone.txt', $message['contact']['phone_number']);
                    } else {
                        file_put_contents('users/phone.txt', $text);
                    }
                    showDeliveryType();
                    break;
                case 'location':
                    if ($message['location']['latitude'] != "" ) {
                        file_put_contents('order/location.txt', $message['location']['latitude']." ".$message['location']['longitude']."\r\n", FILE_APPEND );
                        take();
                    }
                    break;
            }
        }
        break;
}

function showStart()
{
    global $telegram, $chat_id;

    file_put_contents('users/step.txt', 'start');

    $option = array(
        //First row
        array($telegram->buildKeyboardButton("🍯 Batafsil ma'lumot")),
        //Second row
        array($telegram->buildKeyboardButton("🍯 Buyurtma berish"))
    );
    $keyb = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'text' => "Assalom alaykum!
Ushbu bot orqali siz BeeO asal-arichilik firmasidan tabiiy asal va  asal mahsulotlarini sotib olishingiz mumkin!");
    $telegram->sendMessage($content);
    $content = array('chat_id' => $chat_id, 'disable_web_page_preview' => false, 'reply_markup' => $keyb, 'text' => "Mening ismim Jamshid, ko`p yillardan beri oilaviy arichilik bilan shug`illanib kelamiz!
BeeO -asalchilik firmamiz mana 3 yildirki, Toshkent shahri aholisiga toza, tabiiy asal yetkizib bermoqda va ko`plab xaridorlarga ega bo`ldik, shukurki, shu yil ham arichiligimizni biroz kengaytirib siz azizlarning ham dasturxoningizga tabiiy-toza asal yetkazib berishni niyat qildik!");
    $telegram->sendMessage($content);
}

function showAbout()
{
    global $telegram, $chat_id;
    $content = array('chat_id' => $chat_id, 'text' => "Biz haqimizda ma'lumot. <a href='https://telegra.ph/Biz-haqimizda-05-12'>Havola</a>", 'parse_mode' => "html");
    $telegram->sendMessage($content);
}

function showOrder()
{
    global $telegram, $chat_id, $filePath;

    file_put_contents($filePath, 'order');

    $option = array(
        array($telegram->buildKeyboardButton("1kg - 50 000 so'm")),
        array($telegram->buildKeyboardButton("1.5kg (1L) - 75 000 so'm")),
        array($telegram->buildKeyboardButton("4.5kg (3L) - 220 000 so'm")),
        array($telegram->buildKeyboardButton("7.5kg (5L) - 370 000 so'm")),
        array($telegram->buildKeyboardButton("🔙 Orqaga")),

    );
    $keyb = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Buyurtma berish uchun hajmlardan birini tanlang yoki o'zingiz hohlagan hajmni kiriting.");
    $telegram->sendMessage($content);
}

function showInputContact()
{
    global $telegram, $chat_id, $message;

    file_put_contents('users/step.txt', 'phone');

    $option = array(
        array($telegram->buildKeyboardButton("Raqamni jo'natish", $request_contact = true)),
        array($telegram->buildKeyboardButton("🔙 Orqaga")),
    );
    $keyb = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => 'Hajm tanlandi, endi telefon raqamingnizni kiritsangiz.');
    $telegram->sendMessage($content);
}

function showDeliveryType()
{
    global $telegram, $chat_id;

    $option = array(
        array($telegram->buildKeyboardButton("✈️ Yetkazib berish ✈")),
        array($telegram->buildKeyboardButton("🍯 Borib olish 🍯")),
    );
    $keyb = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Bizda Toshkent shahri bo'ylab yetkazib berish xizmati mavjud. Yoki, o'zingiz tashrif buyurib olib ketishingiz mumkin!
Manzil: Toshkent sh, Olmazor tum. Talabalar shaharchasi.");
    $telegram->sendMessage($content);
}

function showInputLocation()
{
    global $telegram, $chat_id;

    file_put_contents('users/step.txt', 'location');

    $option = array(
        array($telegram->buildKeyboardButton("Lokatsiya jo'natish", $request_location = true)),
        array($telegram->buildKeyboardButton("Lokatsiya jo'nata olmayman")),
    );
    $key = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $key, 'text' => "Yaxshi, endi, lokatsiya jo'nating!");
    $telegram->sendMessage($content);
}

function showAccepted(){
    global $telegram, $chat_id;
    $option = array(
        array($telegram->buildKeyboardButton("Boshqa buyurtma berish")),
    );
    $keyb = $telegram->buildKeyBoard($option, $onetime=false, $resize=true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb,  'text' => "Sizning buyurtmangiz qabul qilindi. Tez orada siz bilan bog'lanamiz. Murojaatingiz uchun rahmat! 😊");
    $telegram->sendMessage($content);
}

function sendMessageJson()
{
    global $telegram, $data;
    $telegram->sendMessage([
        'chat_id' => $telegram->ChatID(),
        'text' => json_encode($data, JSON_PRETTY_PRINT)
    ]);
}

printAllData();

function printAllData() {
    global $db;

    $result = $db->query("SELECT * FROM `users`");

    while ($arr = $result->fetch_assoc()) {
        var_dump($arr);
        if ($arr['ru'] != NULL)
        if (isset($arr['data_json'])) {
//            print $arr['data_json'];
            print "<br/>";
        }
    }




}