<?php 
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\FileUpload;

final class InputOutputConsistentTest extends TestCase {

    function testInputOutputConsistentFields()
    {

        $input = [
            'field_0' => 'string',
            'field_1' => false,
            'field_2' => [ 1,2,3,4 ],
            'field_3' => 84,
            'field_4' => '200',
            'field_5' => new \DateTime('2020-1-23'),
        ];

        $validator = new Cleaner($input);

        $validated = $validator->validate([
            'field_0' => 'required|string',
            'field_1' => 'required|boolean',
            'field_2' => 'required|array',
            'field_3' => 'required|integer',
            'field_4' => 'required|numeric',
            'field_5' => 'required|date',
        ]);

        $this->assertTrue($validator->isValid());

        $this->assertArrayHasKey('field_0', $validated);
        $this->assertArrayHasKey('field_1', $validated);
        $this->assertArrayHasKey('field_2', $validated);
        $this->assertArrayHasKey('field_3', $validated);
        $this->assertArrayHasKey('field_4', $validated);
        $this->assertArrayHasKey('field_5', $validated);

        $this->assertEquals($validated['field_0'],$input['field_0']);
        $this->assertEquals($validated['field_1'],$input['field_1']);
        $this->assertEquals($validated['field_2'],$input['field_2']);
        $this->assertEquals($validated['field_3'],$input['field_3']);
        $this->assertEquals($validated['field_4'],$input['field_4']);
        $this->assertEquals($validated['field_5'],$input['field_5']);

    }
}
