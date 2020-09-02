<?php
namespace Itstructure\Mult\Helpers;

use Illuminate\Database\Eloquent\Model;
use Itstructure\Mult\Models\Language;

/**
 * Class MultilingualHelper
 * @package Itstructure\Mult\Helpers
 */
class MultilingualHelper
{
    /**
     * @param array $fields
     * @param callable $transformer
     * @param array $shortLanguageList
     * @return array
     */
    public static function fieldsTransformer(array $fields, callable $transformer, array $shortLanguageList = []): array
    {
        $output = [];

        if (empty($shortLanguageList)) {
            $shortLanguageList = Language::shortLanguageList();
        }

        foreach ($fields as $fieldKey => $fieldValue) {
            foreach ($shortLanguageList as $shortLang) {
                $output[$fieldKey.'_'.$shortLang] = call_user_func($transformer, $fieldValue, $shortLang);
            }
        }

        return $output;
    }

    /**
     * @param Model $model
     * @param array $attributes
     * @return Model
     */
    public static function fill(Model $model, array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }
}