<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = ['value' => 'array'];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // Company Settings
    public static function getCompanyName()
    {
        return static::get('company_name', 'HazWatch');
    }

    public static function getCompanyRegistrationNumber()
    {
        return static::get('company_registration_number', '');
    }

    // DUAL LOGO SUPPORT
    public static function getCompanyLogoLight()
    {
        return static::get('company_logo_light');
    }

    public static function getCompanyLogoDark()
    {
        return static::get('company_logo_dark');
    }

    public static function getCompanyAddress()
    {
        return static::get('company_address', '');
    }

    public static function getCompanyPhone()
    {
        return static::get('company_phone', '');
    }

    public static function getCompanyEmail()
    {
        return static::get('company_email', '');
    }

    // Banking Settings
    public static function getBankName()
    {
        return static::get('bank_name', '');
    }

    public static function getBankAccountNumber()
    {
        return static::get('bank_account_number', '');
    }

    public static function getBankIban()
    {
        return static::get('bank_iban', '');
    }

    public static function getBankSwift()
    {
        return static::get('bank_swift', '');
    }

    // Currency helpers
    public static function getDefaultCurrency()
    {
        return static::get('default_currency', 'USD');
    }

    public static function getSupportedCurrencies()
    {
        return static::get('supported_currencies', ['USD', 'EUR', 'GBP', 'RON']);
    }

    public static function getExchangeRates()
    {
        return static::get('exchange_rates', []);
    }
}
