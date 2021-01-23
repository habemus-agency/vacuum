<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\Filters\CustomFilter;
use Habemus\Vacuum\FileUpload;

final class FileUploadsTest extends TestCase {

    function test(){

        $source = __DIR__ . '/test-files/habemus.png';
        $file_path = __DIR__ . '/test-files/test_file.pdf';

        $this->assertTrue(copy($source,$file_path));

        $file_data = [ 
            'name' => 'testfile.pdf',
            'tmp_name' => $file_path,
            'type' => mime_content_type($file_path),
            'error' => 0,
            'size' => filesize($file_path),
        ];

        $this->assertFalse(FileUpload::isNativeFileUploadData($file_data));

        $testfile = new FileUpload($file_data);

        $validator = new Cleaner([
            'test_file' => $testfile,
        ]);

        $validated = $validator->validate([
            'test_file' => 'required|file|max:100000|mimes:png',
        ]);


        $this->assertTrue($validator->isValid());

    }
}