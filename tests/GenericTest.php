<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\Filters\CustomFilter;

final class GenericTest extends TestCase {

    function test()
    {
        $faker = Faker\Factory::create('it_IT');

        $fake_data = [
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'postcode' => $faker->postcode,
            'email' => $faker->freeEmail,
            'url' => $faker->url,
            'slug' => $faker->slug,
            'date' => $faker->date,
            'iban' => $faker->iban(null),
            'text' => $faker->realText(),
        ];

        $input = array_merge($_POST,$_FILES,$fake_data);

        $sanitizer = function($value){ return str_replace(' ','',$value);};

        $validator = new Cleaner($input,['postcode' => 'Il campo in oggetto deve essere un numero di telefono.'],function($value){
            return strip_tags($value);
        });



        $data = $validator->validate([
            'name' => [
                'required',
                new CustomFilter(function($value){
                    return true;
                },function($value){
                    return str_replace([' ',',','.'],'',$value);
                }),
                'alpha'
            ],
            'postcode' => 'required|digits:5',
            'email' => 'required|email',
            'url' => 'required|url',
            'slug' => [
                'string',
                'slug',
            ],
            'date' => 'nullable|date',
            'iban' => 'required|alpha_num',
            'text' => [
                'required',
                new CustomFilter(function($value){ return is_string($value);})
            ],
        ]);

        $this->assertTrue($validator->isValid());

        /*
        //check data
        echo "<br><br>INPUT:";
        var_dump($input);

        echo "<br><br>VALIDATED:";
        var_dump($data);

        echo "<br><br>ERRORS:";
        var_dump($validator->errors());
        echo "<br><br>";
        */
    }
}