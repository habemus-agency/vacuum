<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Habemus\Vacuum\Cleaner;
use Habemus\Vacuum\Filters\CustomFilter;
use Habemus\Vacuum\FileUpload;
use Habemus\Vacuum\File;

final class FileUploadsTest extends TestCase {

    function test(){

        $file_path = __DIR__ . '/test-files/habemus.png';

        $file_data = [ 
            'name' => 'testfile.pdf',
            'tmp_name' => $file_path,
            'type' => mime_content_type($file_path),
            'error' => 0,
            'size' => filesize($file_path),
        ];

        $this->assertTrue(FileUpload::isNativeFileUploadData($file_data));

        $validator = new Cleaner([
            'test_file' => new File($file_path),
        ]);

        $validated = $validator->validate([
            'test_file' => 'required|file|max:100000|mimes:docx',
        ]);


        $this->assertFalse($validator->isValid());


        $validator = new Cleaner([
            'test_file' => $file_data,
        ]);


        $validated = $validator->validate([
            'test_file' => 'required|file|max:100000|mimes:png',
        ]);


        $this->assertFalse($validator->isValid());

    }
}