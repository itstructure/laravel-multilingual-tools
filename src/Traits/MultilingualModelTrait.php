<?php
namespace Itstructure\Mult\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Database\Eloquent\Collection;
use Itstructure\Mult\Classes\MultilingualMigration;
use Itstructure\Mult\Models\Language;

/**
 * Class MultilingualModelTrait
 *
 * @property Collection|Model[] $translateList
 *
 * @method HasMany hasMany($related, $foreignKey = null, $localKey = null)
 * @method HasOne hasOne($related, $foreignKey = null, $localKey = null)
 * @method bool isFillable($key)
 *
 * @package Itstructure\Mult\Traits
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
trait MultilingualModelTrait
{
    /**
     * Container for temporary storage of translation data.
     * @var array
     */
    protected $tmpTranslateStorage = [];

    /**
     * @var null|HasMany
     */
    protected $tmpTranslateList = null;

    /**
     * Return related translate model name.
     * @return string
     */
    public static function translateModelClass()
    {
        $primaryClass = new \ReflectionClass(static::class);
        return $primaryClass->getNamespaceName() . '\\' . $primaryClass->getShortName() . 'Language';
    }

    /**
     * Override model magic getter. Return translate for field.
     * Example: if we try $model->title_en, we will get 'title' in english.
     * @param string $name field name.
     * @return mixed|null
     */
    public function __get($name)
    {
        if (false === $this->isMultiLanguageField($name)) {
            return parent::__get($name);
        }

        $nameArray = explode('_', $name);
        $lang = array_pop($nameArray);
        $field = implode('_', $nameArray);

        foreach ($this->translateList()->get() as $translate) {
            if ($translate->language->short_name === $lang) {
                return $translate->{$field};
            }
        }

        return null;
    }

    /**
     * Override model magic setter. Set translation for the field.
     * For example $model->title_en  will save title field in translate model where
     * languages_id => record in language with 'en' locale.
     * @param string $name  name of field.
     * @param mixed  $value value to be stored in field.
     * @return void
     */
    public function __set($name, $value)
    {
        if (false === $this->isMultiLanguageField($name)) {
            if ($this->isFillable($name)) {
                parent::__set($name, $value);
            }
            return;
        }

        $nameArray = explode('_', $name);
        $lang = array_pop($nameArray);
        $field = implode('_', $nameArray);

        $this->tmpTranslateStorage[$lang][$field] = $value;
    }

    /**
     * Return key name of relation between main table and translations table.
     * @return string
     */
    public function keyToMainModel()
    {
        /* @var Model $this */
        return $this->table . '_id';
    }

    /**
     * Return translations table name.
     * @return string
     */
    public function translateTableName()
    {
        /* @var Model $this */
        return $this->table . '_' . MultilingualMigration::LANGUAGE_TABLE_NAME;
    }

    /**
     * Return related translated queries.
     * @return HasMany
     */
    public function translateList()
    {
        if ($this->tmpTranslateList == null) {
            $this->tmpTranslateList = $this->hasMany(static::translateModelClass(), $this->keyToMainModel(), MultilingualMigration::PRIMARY_KEY_NAME);
        }

        return $this->tmpTranslateList;
    }

    /**
     * Returns default translate. If field name is given, we can take an alternative
     * translate when default translate value is empty.
     * @param string|null $field
     * @param string $ifNotExistsValue
     * @return string
     */
    public function defaultTranslate(string $field = null, string $ifNotExistsValue = '-')
    {
        /* @var Model $this */
        $mainRequest = $this->hasOne(static::translateModelClass(), $this->keyToMainModel(), MultilingualMigration::PRIMARY_KEY_NAME);

        $defaultTranslate = $mainRequest->where([
            MultilingualMigration::keyToLanguageTable() => Language::firstWhere('default', 1)->{MultilingualMigration::PRIMARY_KEY_NAME}
        ]);

        if ($field !== null && $defaultTranslate->where($field, '!=', '')->whereNotNull($field)->count() == 0) {
            $result = $mainRequest->where($field, '!=', '')->whereNotNull($field)->first();

            return empty($result) ? $ifNotExistsValue : $result->{$field};
        }

        return $field === null ? $defaultTranslate->first() : $defaultTranslate->first()->{$field};
    }

    /**
     * Override model method to save all translations after main model saved.
     * @return void
     */
    protected static function booted()
    {
        static::saved(function($model) {
            /* @var static $model */
            foreach ($model->tmpTranslateStorage as $lang => $fields) {
                $langModel = Language::firstWhere('short_name', $lang);

                $translateModelQuery = forward_static_call_array([static::translateModelClass(), 'where'], [$model->keyToMainModel(), $model->{MultilingualMigration::PRIMARY_KEY_NAME}]);
                $translateModelQuery->where(MultilingualMigration::keyToLanguageTable(), $langModel->{MultilingualMigration::PRIMARY_KEY_NAME});
                $updated = $translateModelQuery->update($fields);

                if (!$updated) {
                    forward_static_call_array([static::translateModelClass(), 'create'], [
                        array_merge($fields, [
                            $model->keyToMainModel() => $model->{MultilingualMigration::PRIMARY_KEY_NAME},
                            MultilingualMigration::keyToLanguageTable() => $langModel->{MultilingualMigration::PRIMARY_KEY_NAME}
                        ])
                    ]);
                }
            }
        });
    }

    /**
     * Check for multi-language mode of field.
     * @param string $name name of field to be checked.
     * @return boolean
     */
    protected function isMultiLanguageField($name): bool
    {
        if (false === strpos($name, '_')) {
            return false;
        }

        $nameArray = explode('_', $name);
        $lang = array_pop($nameArray);

        if (null === $lang) {
            return false;
        }

        if (false === in_array($lang, Language::shortLanguageList(), true)) {
            return false;
        }

        return true;
    }
}
