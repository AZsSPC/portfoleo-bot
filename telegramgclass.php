<?php

class TG
{
    public $token = '';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function send($id, $message, $reply_to_message_id, $keyboard)
    {
        //Удаление клавы
        if ($keyboard == "DEL") $keyboard = array('remove_keyboard' => true);

        if ($keyboard) { //Отправка клавиатуры
            $encodedMarkup = json_encode($keyboard);
            $data = array(
                'chat_id' => $id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => $encodedMarkup
            );
        } else { //Отправка сообщения
            $data = array(
                'chat_id' => $id,
                'text' => $message,
                'parse_mode' => 'HTML',
            );
        }
        if ($reply_to_message_id != 0) $data['reply_to_message_id'] = $reply_to_message_id;

        $out = $this->request('sendMessage', $data);
        return $out;
    }

    public function photo($id, $message, $reply_to_message_id, $keyboard)
    {
        $photo_message = "\n" . preg_replace('/<.+?>/gm', '', $message) . "\n";
        $font_file = './img_font.ttf';
        $font_size = 10;
        $img_y = sizeof(explode("\n", $photo_message)) * $font_size * 1.35;
        $img_x = 0;
        $str_arr = preg_split("/\n/", $photo_message);
        foreach ($str_arr as $str_val) if ($img_x < strlen($str_val)) $img_x = strlen($str_val);
        $img_x = $font_size * ($img_x * 0.44 + 2);

        $im = imagecreatetruecolor($img_x, $img_y);
        $white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
        $black = imagecolorallocate($im, 0x00, 0x00, 0x00);

        imagefilledrectangle($im, 0, 0, $img_x, $img_y, $white);
        imagefttext($im, $font_size, 0, $font_size, $font_size, $black, $font_file, $photo_message);
        imagejpeg($im, 'gen_image.jpeg');
        imagedestroy($im);

        //Удаление клавы
        if ($keyboard == "DEL") $keyboard = array('remove_keyboard' => true);

        if ($keyboard) { //Отправка клавиатуры
            $encodedMarkup = json_encode($keyboard);
            
            $data = array(
                'chat_id' => $id,
                'photo' => 'https://dpos.space/portfoleo-bot/gen_image.jpeg',
                'caption' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => $encodedMarkup
            );
        } else { //Отправка сообщения
            $data = array(
                'chat_id' => $id,
                'photo' => 'https://dpos.space/portfoleo-bot/gen_image.jpeg',
                'caption' => $message,
                'parse_mode' => 'HTML',
            );
        }
        if ($reply_to_message_id != 0) $data['reply_to_message_id'] = $reply_to_message_id;

        $out = $this->request('sendPhoto', $data);
        return $out;
    }

    public function voice($id, $voice, $reply_to_message_id, $keyboard)
    {
        //Удаление клавы
        if ($keyboard == "DEL") $keyboard = array('remove_keyboard' => true);

        if ($keyboard) {//Отправка клавиатуры
            $encodedMarkup = json_encode($keyboard);
            $data = array(
                'chat_id' => $id,
                'voice' => $voice,
                'reply_markup' => $encodedMarkup
            );
        } else {//Отправка сообщения
            $data = array(
                'chat_id' => $id,
                'file_id' => $voice,
            );
        }
        if ($reply_to_message_id != 0) $data['reply_to_message_id'] = $reply_to_message_id;

        $out = $this->request('sendMessage', $data);
        return $out;
    }

    public function edit($id, $msg_id, $message, $keyboard)
    {
        //Удаление клавы
        if ($keyboard == "DEL") $keyboard = array('remove_keyboard' => true);

        if ($keyboard) {//Отправка клавиатуры
            $encodedMarkup = json_encode($keyboard);

            $data = array(
                'chat_id' => $id,
                'message_id' => $msg_id,
                'text' => $message,
                'reply_markup' => $encodedMarkup
            );
        } else {
            //Отправка сообщения
            $data = array(
                'chat_id' => $id,
                'message_id' => $msg_id,
                'text' => $message
            );
        }

        $out = $this->request('editMessageText', $data);
        return $out;
    }

    public function forward($id, $from_chat_id, $msg_id)
    {
        $data = array(
            'chat_id' => $id,
            'from_chat_id' => $from_chat_id,
            'disable_notification' => false,
            'message_id' => $msg_id
        );

        $out = $this->request('forwardMessage', $data);
        return $out;
    }

    public function getPhoto($data)
    {
        $out = $this->request('getFile', $data);
        return $out;
    }

    public function savePhoto($url, $puth)
    {
        $ch = curl_init('https://api.telegram.org/file/bot' . $this->token . '/' . $url);
        $fp = fopen($puth, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    public function request($method, $data = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->token . '/' . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $out = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $out;
    }
}

