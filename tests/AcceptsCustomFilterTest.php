<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\Filters\CustomFilter;

final class AcceptsCustomFilterTest extends TestCase {

    function testAcceptsCustomFilter()
    {
        $input = [
            'field' => "The Cat is on the table",
            'another_field' => "The Apple is on the table",
        ];

        $validator = new Cleaner($input);

        $myFilter = new CustomFilter(function($value){

            if(strpos($value,'Cat') !== false){
                return true;
            }

            return false;
        });

        $validator->validate([
            'field' => [
                'required',
                'string',
                $myFilter,
            ],
        ]);

        $this->assertTrue($validator->isValid());


        $myFilter2 = new CustomFilter(function($value){

            if(strpos($value,'Apple') !== false){
                return true;
            }

            return false;
        },function ($value){
            return str_replace(' ','',$value);
        },true);

        $validator->validate([
            'another_field' => [
                'required',
                'string',
                $myFilter2,
                'alpha'
            ],
        ]);

        $this->assertTrue($validator->isValid());

    }
}