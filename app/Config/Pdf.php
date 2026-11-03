<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pdf extends BaseConfig
{
    public bool $enableWatermarkFooter = true;
    public string $appName = 'SIGAPP';
    public bool $includeUsername = true;
    public bool $includePageNumbers = true;
}
