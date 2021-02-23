# MULT
## Laravel multilingual tools

[![Latest Stable Version](https://poser.pugx.org/itstructure/laravel-multilingual-tools/v/stable)](https://packagist.org/packages/itstructure/laravel-multilingual-tools)
[![Latest Unstable Version](https://poser.pugx.org/itstructure/laravel-multilingual-tools/v/unstable)](https://packagist.org/packages/itstructure/laravel-multilingual-tools)
[![License](https://poser.pugx.org/itstructure/laravel-multilingual-tools/license)](https://packagist.org/packages/itstructure/laravel-multilingual-tools)
[![Total Downloads](https://poser.pugx.org/itstructure/laravel-multilingual-tools/downloads)](https://packagist.org/packages/itstructure/laravel-multilingual-tools)
[![Build Status](https://scrutinizer-ci.com/g/itstructure/laravel-multilingual-tools/badges/build.png?b=master)](https://scrutinizer-ci.com/g/itstructure/laravel-multilingual-tools/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itstructure/laravel-multilingual-tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itstructure/laravel-multilingual-tools/?branch=master)

![MULT package label](https://github.com/itstructure/laravel-multilingual-tools/blob/master/mult.png)

## 1 Introduction

**MULT** - Package for the Laravel framework to content manage with different languages.

For example, you can store pages in English, Russian, French, German and some another languages...

It is you who add new languages to your application.

All multilingual fields will be with a language postfix, as in example:

`title_en`, `description_en`, `content_en`

`title_ru`, `description_ru`, `content_ru`, e t. c.

## 2 Dependencies

- laravel 8+
- php >= 7.3
- composer

## 3 Installation

**Note!**

Version **2.x** is for laravel **8**.

Version **1.x** is for laravel **7**. You can use branch `laravel7-mult` with **1.x** versions.

### 3.1 General installation from remote repository

Run the composer command:

`composer require itstructure/laravel-multilingual-tools "~2.0.1"`

### 3.2 If you are testing this package from local server directory

In application `composer.json` file set the repository, as in example:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laravel-multilingual-tools"
    }
],
```

Here,

**../laravel-multilingual-tools** - directory name, which has the same directory level as your project application and contains MULT package.

Then run command:

`composer require itstructure/laravel-multilingual-tools:dev-master --prefer-source`

### 3.3 App config

Add to application `config/app.php` file to section **providers**:

```php
Itstructure\Mult\MultServiceProvider::class,
```

### 3.4 Next internal installation steps

1. Publish files.
        
    - To publish migrations run command:
            
        `php artisan mult:publish --only=migrations`
        
        It stores migration file to `database/migrations` folder. There is a migration to create **languages** table.
        
    - To publish seeder run command:
            
        `php artisan mult:publish --only=seeders`
        
        It stores seeder files to `database/seeders` folder. There is a seeder to create a first **English** language DB entry as a default.
        
    - To publish all parts run command without `only` argument:
    
        `php artisan mult:publish`
        
    Else you can use `--force` argument to rewrite already published files.
    
2. Run command to run migration and seed:

    `php artisan mult:database`
    
    The next will be acted:
    
    - A table **languages** will be created.
    
    - A first **English** language will be stored as a default.
    
    Or optional:
    
    To run just migration `php artisan mult:database --only=migrate`
    
    To run just seed `php artisan mult:database --only=seed`
    
    - Alternative variant for seeders.
    
        You can set published `MultSeeder` seeder class in to a special `DatabaseSeeder`:
            
        ```php
        use Illuminate\Database\Seeder;
        ```
        
        ```php
        class DatabaseSeeder extends Seeder
        {
            public function run()
            {
                $this->call(MultSeeder::class);
            }
        }
        ```
        
        and run command: `php artisan db:seed`.

## 4 Usage

**Notes**:

- There are no controllers, views, routes and another CRUD elements to manage languages. There is just a model `Language`. This CRUD you must to create by yourself in your application.

- There are no controllers, models, views, routes and another CRUD elements to manage entity content. There are just base classes, which are written below. CRUDs you must to create by yourself in your application.

### 4.1 Migration part

First, before a develop multilingual application, make migrations with extending from `MultilingualMigration` base class.

Example for **pages** table:

```php
use Illuminate\Database\Schema\Blueprint;
use Itstructure\Mult\Classes\MultilingualMigration;
```

```php
class CreatePagesTable extends MultilingualMigration
{
    public function up()
    {
        $this->createMultilingualTable('pages', function (Blueprint $table) {
            $table->string('title', 64);
            $table->text('description')->nullable();
            
        }, function (Blueprint $table) {
            $table->unsignedTinyInteger('active')->default(0)->index();
            $table->string('alias', 64)->index();
        });
    }
    
    public function down()
    {
        $this->dropMultilingualTable('pages');
    }
}
```

Here,

**createMultilingualTable()** method provides:

1. First argument: table name.

2. Second argument: a callable with multilingual fields.

3. Third argument: a callable with simple fields.

After applying a migration, two tables will be created automatically:

- **pages** - to store a simple data.

- **pages_languages** - to store translates, some entries for concrete **pages** entry.

**Note:** Timestamps created automatically for both tables.

And the next special columns for **pages_languages** table with foreign keys will be created automatically:

- column **pages_id**. Foreign key to **pages** table `pages_languages(pages_id) -> pages(id)`

- column **languages_id**. Foreign key to **languages** table `pages_languages(languages_id) -> languages(id)`

Example with already stored data:

`Main table "pages"`

    | id | active |    alias    |      created_at     |      updated_at     |
    |----|--------|-------------|---------------------|---------------------|
    | 1  |    1   | first-page  | 2020-01-14 18:06:33 | 2020-01-14 18:06:33 |
    | 2  |    1   | second-page | 2020-01-14 18:10:00 | 2020-01-14 18:10:00 |
    | 3  |    0   | third-page  | 2020-01-14 19:05:15 | 2020-01-14 19:05:15 |
    
`Translate table "pages_languages"`

    | pages_id | languages_id |    title   |      description     |      created_at     |      updated_at     |
    |----------|--------------|------------|----------------------|---------------------|---------------------|
    |    1     |      1       | Page 1     |     Description 1    | 2020-01-14 18:06:33 | 2020-01-14 18:06:33 |
    |    1     |      2       | Страница 1 |     Описание 1       | 2020-01-14 18:06:33 | 2020-01-14 18:06:33 |
    |    2     |      1       | Page 2     |     Description 2    | 2020-01-14 18:10:00 | 2020-01-14 18:10:00 |
    |    3     |      1       | Page 3     |     Description 3    | 2020-01-14 19:05:15 | 2020-01-14 19:05:15 |
    |    3     |      2       | Страница 3 |     Описание 3       | 2020-01-14 19:05:15 | 2020-01-14 19:05:15 |
    
`Language table "languages"`

    | id | locale | short_name |  name   | default |      created_at     |      updated_at     |
    |----|--------|------------|---------|---------|---------------------|---------------------|
    | 1  | en-US  |     en     | English |    1    | 2020-01-14 18:06:33 | 2020-01-14 18:06:33 |
    | 2  | ru-RU  |     ru     | Русский |    0    | 2020-01-14 18:10:00 | 2020-01-14 18:10:00 |

### 4.2 Model part

**Notes:**

- Access to multilingual value from a model entry is by using a language postfix. Example:

    ```php
    $model = Page::findOrFail($id);
    echo $model->title_en;
    ```

- Set a new multilingual value for a model is by using a language postfix. Example:

    ```php
    $model = Page::findOrFail($id);
    $model->title_en = 'New title value';
    $model->save();
    ```

#### 4.2.1 Main simple model

Create a main model for base table and use `MultilingualModelTrait`.

Example model for **pages** table:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Itstructure\Mult\Traits\MultilingualModelTrait;
```

```php
class Page extends Model
{
    use MultilingualModelTrait;
    
    protected $table = 'pages';
    
    protected $fillable = ['active', 'alias'];
}
```

#### 4.2.2 Translate model

Create a model for translates table.

Example model for **pages_languages** table:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
```

```php
class PageLanguage extends Model
{
    protected $table = 'pages_languages';
    
    protected $fillable = ['pages_id', 'languages_id', 'title', 'description'];
    
    public function page() // It is not necessary to create
    {
        return $this->hasOne(Page::class, 'id', 'pages_id');
    }
    
    public function language() // It is not necessary to create
    {
        return $this->hasOne(Language::class, 'id', 'languages_id');
    }
}
```

It is not necessary to create relation methods here, such as: `page()`, `language()`. It is additional.

### 4.3 Validation requests part

This request classes can be useful in controller's methods.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Itstructure\Mult\Models\Language;
use Itstructure\Mult\Helpers\MultilingualHelper;
```

```php
class StorePageRequest extends FormRequest
{
    protected $shortLanguageList = [];
    
    public function __construct()
    {
        parent::__construct();
        
        $this->shortLanguageList = Language::shortLanguageList();
    }
    
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        $multilingualRules = MultilingualHelper::fieldsTransformer([
            'title' => 'required|string|min:3|max:64',
            'description' => 'nullable|string'
            
        ], function ($fieldValue) {
            return $fieldValue;
            
        }, $this->shortLanguageList);
        
        return array_merge([
            'active' => 'required|numeric',
            'alias' => 'required|string|min:3|max:64'
            
        ], $multilingualRules);
    }
    
    public function attributes()
    {
        $multilingualAttributes = MultilingualHelper::fieldsTransformer([
            'title' => __('messages.title'),
            'description' => __('messages.description')
            
        ], function ($fieldValue) {
            return $fieldValue;
            
        }, $this->shortLanguageList);
        
        return array_merge([
            'active' => __('messages.activity'),
            'alias' => __('messages.alias')
            
        ], $multilingualAttributes);
    }
}
```

Here `roles()` method makes the next result:

```php
[
    "active" => "required|numeric",
    "alias" => "required|string|min:3|max:64",
    "title_en" => "required|string|min:3|max:64",
    "title_ru" => "required|string|min:3|max:64",
    "description_en" => "nullable|string",
    "description_ru" => "nullable|string"
]
```

### 4.4 Controller part

Short page controller example just to create entry:

```php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Itstructure\Mult\Models\Language;
use Itstructure\Mult\Helpers\MultilingualHelper;
use App\Models\Page;
use App\Http\Requests\StorePageRequest;
```

```php
class PageController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function create()
    {
        $languageList = Language::languageList();
        
        return view('page.create', compact('languageList'));
    }
    
    public function store(StorePageRequest $request)
    {
        MultilingualHelper::fill(new Page(), $request->all())->save();
        
        return redirect()->route('page_list');
    }
}
```

### 4.5 View template part

Short example for `page.create` blade view template:

```blade
<form action="{{ route('page_store') }}" method="post">
    <ul class="nav nav-tabs">
        @foreach($languageList as $langModel)
            <li class="nav-item">
                <a class="nav-link @if($langModel->default == 1)active @endif" data-toggle="tab" href="#lang_{{ $langModel->short_name }}">
                    {{ $langModel->name }}
                </a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content my-2">
        @foreach($languageList as $langModel)
            <div class="tab-pane fade @if($langModel->default == 1)show active @endif" id="lang_{{ $langModel->short_name }}">
                <div class="row">
                    <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
                        <div class="form-group">
                            <label for="page_title_{{ $langModel->short_name }}">{!! __('messages.title') !!}</label>
                            <input id="page_title_{{ $langModel->short_name }}"
                                   type="text"
                                   class="form-control @if ($errors->has('title_'.$langModel->short_name)) is-invalid @endif"
                                   name="title_{{ $langModel->short_name }}"
                                   value="{{ old('title_'.$langModel->short_name, !empty($model) ? $model->{'title_'.$langModel->short_name} : null) }}">
                            @if ($errors->has('title_'.$langModel->short_name))
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->first('title_'.$langModel->short_name) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
                        <div class="form-group">
                            <label for="page_description_{{ $langModel->short_name }}">{!! __('messages.description') !!}</label>
                            <input id="page_description_{{ $langModel->short_name }}"
                                   type="text"
                                   class="form-control @if ($errors->has('description_'.$langModel->short_name)) is-invalid @endif"
                                   name="description_{{ $langModel->short_name }}"
                                   value="{{ old('description_'.$langModel->short_name, !empty($model) ? $model->{'description_'.$langModel->short_name} : null) }}">
                            @if ($errors->has('description_'.$langModel->short_name))
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->first('description_'.$langModel->short_name) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="row mb-3">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="page_active_input" name="active" value="1" class="custom-control-input" 
                    @if(old('active', !empty($model) ? $model->active : 1) == 1) checked @endif >
                <label class="custom-control-label" for="page_active_input">{!! __('messages.active') !!}</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="page_inactive_input" name="active" value="0" class="custom-control-input" 
                    @if(old('active', !empty($model) ? $model->active : 1) == 0) checked @endif >
                <label class="custom-control-label" for="page_inactive_input">{!! __('messages.inactive') !!}</label>
            </div>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Create</button>
    <input type="hidden" value="{!! csrf_token() !!}" name="_token">
</form>
```

## License

Copyright © 2020-2021 Andrey Girnik girnikandrey@gmail.com.

Licensed under the [MIT license](http://opensource.org/licenses/MIT). See LICENSE.txt for details.
