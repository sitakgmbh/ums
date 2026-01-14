<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ["key", "value", "type", "name", "description"];

    protected $casts = [];

    public function getValueAttribute($value)
    {
        return match ($this->type) {
            "bool"     => (bool) $value,
            "int"      => (int) $value,
			"enum"     => $value,
            "json"     => json_decode($value, true),
            "password" => $value ? Crypt::decryptString($value) : null,
            default    => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes["value"] = match ($this->type) {
            "bool"     => $value ? 1 : 0,
            "json"     => json_encode($value),
            "password" => $value ? Crypt::encryptString($value) : null,
			"enum"     => $value,
            default    => (string) $value,
        };
    }

    public static function getValue(string $key, $default = null)
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = static::where("key", $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
}
