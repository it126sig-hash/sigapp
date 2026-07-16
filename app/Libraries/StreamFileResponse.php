<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Response;
use Config\App;

class StreamFileResponse extends Response
{
    private string $filePath;
    private int $chunkSize;

    public function __construct(string $filePath, int $chunkSize = 1048576)
    {
        parent::__construct(config(App::class));

        $this->filePath = $filePath;
        $this->chunkSize = $chunkSize;
    }

    public function sendBody()
    {
        $handle = fopen($this->filePath, 'rb');
        if ($handle === false) {
            return $this;
        }

        while (!feof($handle)) {
            $chunk = fread($handle, $this->chunkSize);
            if ($chunk === false) {
                break;
            }

            echo $chunk;
            unset($chunk);
            flush();
        }

        fclose($handle);

        return $this;
    }
}
