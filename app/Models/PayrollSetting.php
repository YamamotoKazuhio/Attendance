<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    // 設定値の取得・保存を簡単にするヘルパーメソッド
    public static function getVal($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? json_decode($setting->value, true) : $default;
    }

    public static function setVal($key, $value, $description = null)
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => json_encode($value),
                'description' => $description
            ]
        );
    }
}