<?php
namespace Itstructure\Mult\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 *
 * @property int $default
 *
 * @package Itstructure\Mult\Models
 */
class Language extends Model
{
    /**
     * @var string
     */
    protected $table = 'languages';

    /**
     * @var array
     */
    protected $fillable = ['locale', 'short_name', 'name', 'default'];

    /**
     * List of available languages in short name format.
     * @return array
     */
    public static function shortLanguageList(): array
    {
        return static::pluck('short_name')->toArray();
    }

    /**
     * List of available languages.
     * @return mixed
     */
    public static function languageList()
    {
        return static::get();
    }

    /**
     * Get a default language entry.
     * @return Language|null
     */
    public static function defaultLanguage()
    {
        return static::firstWhere('default', 1);
    }

    /**
     * Set default = 1 just for one entry.
     * @return void
     */
    protected static function booted()
    {
        static::saved(function($model) {
            /* @var Language $model */
            if ($model->default == 1) {

                /* @var Language $default */
                $default = Language::firstWhere('default', 1);

                if (null !== $default){
                    $default->default = 0;
                    $default->save();
                }
            }
        });
    }
}
