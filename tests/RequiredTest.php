<?php 
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\FileUpload;

final class RequiredTest extends TestCase {

    function testRequiredFields()
    {
        $empty_file = new FileUpload([
            'name' => '',
            'tmp_name' => '',
            'type' => '',
            'size' => 0,
            'error' => 4,
        ]);

        $input = [
            'empty_field_0' => '',
            'empty_field_1' => null,
            'empty_field_2' => [],
            'empty_field_3' => "    ",
            'empty_field_4' => $empty_file,
            'filled_field_0' => "Hi, i'm filled",
            'filled_field_1' => 0,
            'filled_field_2' => false,
        ];

        $validator = new Cleaner($input);

        $validated = $validator->validate([
            'empty_field_0' => 'required',
            'empty_field_1' => 'required',
            'empty_field_2' => 'required',
            'empty_field_3' => 'required',
            'empty_field_4' => 'required',
            'empty_field_5' => 'required',
        ]);

        $this->assertFalse($validator->isValid());

        $this->assertEmpty($validated);

        $this->assertCount(6,$validator->errors());




        //second run
        $validated = null;

        $validated = $validator->validate([
            'filled_field_1' => ['required'],
            'filled_field_2' => 'required',
            'filled_field_0' => ['required'],
        ]);

        $this->assertTrue($validator->isValid());

        $this->assertEmpty($validator->errors());

        $this->assertCount(3,$validated);

    }
}
