<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramBotController extends Controller
{
    private $token;
    private $apiUrl;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'] ?? '';

            $user = User::firstOrCreate(['chat_id' => $chatId], ['balance' => 0]);

            if ($text === "/start") {
                $this->sendMessage($chatId, "Xush kelibsiz! Quyidagi tugmalardan foydalaning:", [
                    ["💰 Balans", "🎁 Bonus olish"],
                    ["👥 Do‘stlarni taklif qilish", "📞 Admin bilan bog‘lanish"]
                ]);
            } elseif ($text === "💰 Balans") {
                $this->sendMessage($chatId, "Sizning balansingiz: {$user->balance} so‘m");
            } elseif ($text === "🎁 Bonus olish") {
                $user->increment('balance', 3000);
                $this->sendMessage($chatId, "🎉 Siz 3000 so‘m bonus oldingiz!");
            } elseif ($text === "👥 Do‘stlarni taklif qilish") {
                $this->sendMessage($chatId, "Taklif havolangiz: https://t.me/yourbot?start={$chatId}");
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendMessage($chatId, $text, $buttons = [])
    {
        $keyboard = empty($buttons) ? null : ['keyboard' => $buttons, 'resize_keyboard' => true];
        Http::post("{$this->apiUrl}sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }
}
