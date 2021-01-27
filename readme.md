# Vacuum: Data validation library

Vacuum is a light-weight standalone library for data validation, heavily based on Laravel Validation.

Currently in alpha.


## Installation

```

$ composer require habemus/vacuum-cleaner

```

## Basic Usage: The Cleaner Object

Instantiate Cleaner::class with input data and validate with built in filters.
Cleaner::class accepts only arrays of data as input. Data can be of any native data type and objects. 
Native PHP file arrays will be converted to FileUpload::class objects.

```php
use Habemus\Vacuum\Cleaner;

$validator = new Cleaner(array_merge($_POST,$_FILES));

$validated = $validator->validate([
    'name' => 'required|string|max:25',
    'email' => 'required|email',
    'phone' => 'nullable|digits:11',
    'privacy' => 'required|boolean',
    'quantity' => 'required|integer|gt:1',
    'upload' => 'present|file|mimes:png,jpg|max:2000000',
]);

```

### Results

To check if input has validated use isValid() method. The `validate()` method returns data that has validated succesfully, even if not all of the input data has not been validated.

```php

$validated = $validator->validate([
    ...
]);


if($validator->isValid()){

    //handle $validated data
    ...


}else{

    //display errors
    $errors = $validator->errors();

}
```

## Filters

Filters must be enclosed in an array or separated by `|`. Filter parameters must be specified after `:` and separated by commas `,`.

```php
$validator->validate([
    'field1' => 'filter1|filter2|filter3|...',
    'field2' => 'filter1_with_params:param1,param2,param3|filter2',
    'field3' => [
        'filter1',
        'filter2',
        'filter3_with_params:param1,param2',
    ],
]);
```

### Available filters

* **required** - the field must be present and not null, empty string, empty array or empty file.
* **nullable** - the field can be omitted.
* **present** - the field must be present, but it's content is not checked.
* **bail** - after this filter, stop executing checks as soon as a filter fails.
* **email** - field must be a valid e-mail.
* **url** - field must be a valid url.
* **alpha_num** - field must contain only letters and numbers.
* **alpha** - field must contain only letters.
* **numeric** - field must contain only numbers.
* **digits:*size*** - field must contain only numbers characters and must be exact *size* long.
* **mimes:*extension1,extension2,...*** - field must be a valid FileUpload and match the specified *extensions*. Extension is guessed by file Mime Type and it's binary data.
* **file** - field must be valid FileUpload.
* **size:*value*** - field size must be of exact *value*. Works with arrays, strings and files.
* **in:*value1,value2,...*** - field must be one of the specified values.
* **gte:*value*** - field must be greater than or equal to *value*. Works with numbers.
* **gt:*value*** - field must be greater than *value*. Works with numbers.
* **lt:*value*** - field must be less than *value*. Works with numbers.
* **lte:*value*** - field must be less than or equal to *value*. Works with numbers.
* **min:*value*** - field must be greater than or equal to *value* in size. Works with arrays and files. 
* **max:*value*** - field must be smaller than or equal to *value* in size. Works with arrays and files. 
* **integer** - field must be a valid integer.
* **boolean** - field must be a valid boolean. Values accepted are `true`,`false`,`0`,`1`,`'true'`,`'false'`.
* **string** - field must be a string.
* **date** - field must be a valid date.
* **slug** - field must contain only lowercase letters, numbers and `-`.


### Custom Filter

It's possible to write your own filter using CustomFilter::class. 
The class constructor accepts a `Closure` as parameter.
The Closure must have one $value parameter and must return either `true` on validation or `false` on failure.

```php
use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\Filters\CustomFilter;

$input = [
    'murphy' => "Anything that can go wrong will go wrong",
];

$validator = new Cleaner($input);

$subject = 'wrong';

$data = $validator->validate([
    'murphy' => [
        'required',
        'string',
        new CustomFilter(function($value) use ($subject) {
            if(strpos($value,$subject) !== false){
                return true;
            }

            return false;
        }),
    ], 
]);
```

Custom Filters also has the capability of running sanitize function on data being evaluated.
Just pass the sanitizing function (`Closure`) as second parameter to the constructor.

```php

$mySpaceRemoverFilter = new CustomFilter(
    function($value){
        if(is_string($value)){
            return true;
        }

        return false;
    },
    function($value){
        return str_replace(' ','',$value);
    },
    true
);
```

The third optional parameter (`true` or `false`) is to instruct Cleaner::class instance to execute the sanitizing function after filter validation.
Otherwise sanitizing functions are always executed first.


## The Request Object

The `Request::class` is a small wrapper around the `Cleaner::class` validator instance that speeds up the process of validating HTTP requests data from forms.

```php

use Habemus\Vacuum\Request;

$request = new Request(); //init from $_GET,$_POST,$_FILES global vars
$request = new Request($custom_data); //init with custom data

$validated_data = $request->validate([
    //filters
],$custom_validation_msgs = null,$sanitizer_fn = null); //basically same as Cleaner __construct method

$request->isValid(); // true or false
$request->getErrors(); //populated after validate()
$request->getValidated(); //populated after validate()
$request->getData(); // retrieve input data

```


## FileUpload

This class has some handy methods to help you deal with files uploaded via forms.
When FileUpload::class constructor is called, it can raise RuntimeException when attempting to guess file extension or while renaming it.
When a FileUpload instance is succesfully created, the uploaded file is automatically renamed with it's upload name, avoiding duplications and overwriting.
If you intend to keep the uploaded file it's mandatory to call the method store($destination_path), because the class destructor will delete the file automatically on exit.

### class methods

* **isEmpty()** - checks if file is empty
* **rename($new_path)** - moves file to new path
* **store($path)** - saves file to $path
* **getPath()** - return current file fullpath
* **getFilename()** - returns *filename.ext* without path
* **getExtension()** - returns file extension
* **getMime()** - returns file mime type


## Error Messages

To retrieve messages use `errors()`.

```php

$errors = $validator->errors();

/*
//output
$errors = [
    'field1_name' => [
        'filter1_name' => 'Error Message',
        'filter2_name' => 'Error Message',
    ],
    'field2_name' => [
        'filter1_name' => 'Error Message',
        'filter2_name' => 'Error Message',
    ],
];

*/

```

This way you can check if field is present and which error has occurred.
It's also possibile to specify a custom error message for a specific field by passing an `array` with custom messages as third parameter to Cleaner::class constructor.

```php

$custom_messages = [
    'field1_name' => 'Il campo non è valido!',
    'field2_name' => 'You can use ###field### placeholder.',
];

$validator = new Cleaner($input_array,$custom_messages_array);

```


### Other options

It's possible to sanitize all fields by passing a `Closure` as third parameter on Cleaner init.

```php

$sanitize_all = function($value){

    if(is_string($value)){
        return trim($value);
    }

    return $value;
};

$validator = new Cleaner($input_array, $custom_messages_array, $sanitize_all);

```


Package developed by Habemus Agency in 2021.

https://habemus.agency/it/


