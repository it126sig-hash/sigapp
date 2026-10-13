<?php

namespace App\Libraries\ModuleManager;

/**
 * ModuleScanner
 * 
 * Memindai struktur codebase aktual dan mencocokkannya dengan ModuleCatalog.
 * Mendeteksi routes eksplisit & AutoRoutes, menghitung readiness score, dan melacak status migrasi.
 */
class ModuleScanner
{
    protected string $appPath;
    protected string $publicPath;
    protected string $docsPath;

    public function __construct()
    {
        $this->appPath    = rtrim(APPPATH, '\\/') . DIRECTORY_SEPARATOR;
        $this->publicPath = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR;
        $this->docsPath   = dirname($this->appPath) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    }

    public function scan(): array
    {
        $catalog = ModuleCatalog::getModules();
        $explicitRoutes = $this->parseExplicitRoutes();
        $mappedFiles = [
            'controllers'  => [],
            'services'     => [],
            'repositories' => [],
            'models'       => [],
        ];

        $modulesResult = [];

        foreach ($catalog as $key => $meta) {
            $controllersInfo  = $this->resolveFiles('Controllers', $meta['controllers'] ?? []);
            $servicesInfo     = $this->resolveFiles('Services', $meta['services'] ?? []);
            $repositoriesInfo = $this->resolveFiles('Repositories', $meta['repositories'] ?? []);
            $modelsInfo       = $this->resolveFiles('Models', $meta['models'] ?? []);
            $viewsInfo        = $this->resolveViews($meta['views'] ?? []);
            $assetsJsInfo     = $this->resolveAssets($meta['assets_js'] ?? []);

            // Catat file yang sudah terpetakan
            foreach ($controllersInfo as $c)  { $mappedFiles['controllers'][]  = $c['relative_path']; }
            foreach ($servicesInfo as $s)     { $mappedFiles['services'][]     = $s['relative_path']; }
            foreach ($repositoriesInfo as $r) { $mappedFiles['repositories'][] = $r['relative_path']; }
            foreach ($modelsInfo as $m)       { $mappedFiles['models'][]       = $m['relative_path']; }

            // Bangun inventori route lengkap (Explicit + AutoRoute)
            $moduleRoutes = $this->buildModuleRouteInventory($explicitRoutes, $controllersInfo);

            // Cek status dokumentasi
            $docFile = $this->docsPath . $key . DIRECTORY_SEPARATOR . 'MODULE.md';
            $hasDoc  = file_exists($docFile);

            // Cek Web/API split
            $hasWebApiSplit = $this->checkWebApiSplit($controllersInfo);

            // Hitung skor kesiapan (0-100)
            $hasService    = count($servicesInfo) > 0;
            $hasRepository = count($repositoriesInfo) > 0;
            $hasRoutes     = count($moduleRoutes) > 0;

            $score = 0;
            if ($hasService)       $score += 20;
            if ($hasRepository)    $score += 20;
            if ($hasWebApiSplit)   $score += 20;
            if ($hasRoutes)        $score += 20;
            if ($hasDoc)           $score += 20;

            // Status modul
            $status = 'UNMAPPED';
            if ($hasDoc) {
                $status = ($score >= 80) ? 'REFACTORED' : 'MAPPED';
            }

            // Ekstrak public contracts (Service methods)
            $serviceContracts = $this->extractServiceContracts($servicesInfo);

            $modulesResult[$key] = [
                'name'               => $meta['name'],
                'description'        => $meta['description'],
                'status'             => $status,
                'readiness_score'    => $score,
                'readiness_checklist'=> [
                    'has_service'        => $hasService,
                    'has_repository'     => $hasRepository,
                    'has_web_api_split'  => $hasWebApiSplit,
                    'has_explicit_routes'=> $hasRoutes,
                    'has_documentation'  => $hasDoc,
                ],
                'owner_tables'       => $meta['owner_tables'] ?? [],
                'shared_tables'      => $meta['shared_tables'] ?? [],
                'controllers'        => $controllersInfo,
                'services'           => $servicesInfo,
                'repositories'       => $repositoriesInfo,
                'models'             => $modelsInfo,
                'views'              => $viewsInfo,
                'assets_js'          => $assetsJsInfo,
                'routes'             => $moduleRoutes,
                'service_contracts'  => $serviceContracts,
                'doc_path'           => 'docs/modules/' . $key . '/MODULE.md',
            ];
        }

        // Cari file yang belum terpetakan (unmapped)
        $unmapped = $this->findUnmappedFiles($mappedFiles);

        return [
            'scanned_at'     => date('c'),
            'total_modules'  => count($modulesResult),
            'modules'        => $modulesResult,
            'unmapped_files' => $unmapped,
        ];
    }

    protected function resolveFiles(string $layer, array $fileList): array
    {
        $baseDir = $this->appPath . $layer . DIRECTORY_SEPARATOR;
        $result = [];

        foreach ($fileList as $relPath) {
            $normRelPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath);
            $fullPath = $baseDir . $normRelPath;

            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $lines   = substr_count($content, "\n") + 1;
                $methods = $this->extractPublicMethods($content);

                $result[] = [
                    'relative_path' => 'app/' . $layer . '/' . str_replace('\\', '/', $relPath),
                    'filename'      => basename($normRelPath),
                    'line_count'    => $lines,
                    'public_methods'=> $methods,
                ];
            }
        }

        return $result;
    }

    protected function resolveViews(array $dirList): array
    {
        $result = [];
        $baseViews = $this->appPath . 'Views' . DIRECTORY_SEPARATOR;

        foreach ($dirList as $dir) {
            $path = $baseViews . $dir;
            if (is_dir($path)) {
                $files = glob($path . DIRECTORY_SEPARATOR . '*.php');
                $result[] = [
                    'folder'     => 'app/Views/' . $dir,
                    'file_count' => is_array($files) ? count($files) : 0,
                ];
            }
        }

        return $result;
    }

    protected function resolveAssets(array $assetList): array
    {
        $result = [];
        $baseJs = $this->publicPath . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;

        foreach ($assetList as $jsRel) {
            $norm = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $jsRel);
            $full = $baseJs . $norm;
            if (file_exists($full)) {
                $result[] = [
                    'path'       => 'public/assets/js/' . str_replace('\\', '/', $jsRel),
                    'filename'   => basename($norm),
                    'line_count' => substr_count(file_get_contents($full), "\n") + 1,
                ];
            }
        }

        return $result;
    }

    protected function extractPublicMethods(string $content): array
    {
        $methods = [];
        // Support public function or simple function
        if (preg_match_all('/(?:public\s+)?function\s+([a-zA-Z0-9_]+)\s*\((.*?)\)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $m[1];
                if (in_array($name, ['__construct', '__destruct', '__clone'])) {
                    continue;
                }
                $params = trim(preg_replace('/\s+/', ' ', $m[2]));
                $methods[] = $name . '(' . $params . ')';
            }
        }
        return $methods;
    }

    protected function extractServiceContracts(array $servicesInfo): array
    {
        $contracts = [];
        foreach ($servicesInfo as $s) {
            $contracts[$s['filename']] = $s['public_methods'];
        }
        return $contracts;
    }

    protected function checkWebApiSplit(array $controllers): bool
    {
        if (empty($controllers)) return false;

        $hasApi = false;
        $hasWeb = false;

        foreach ($controllers as $c) {
            $rel = strtolower($c['relative_path']);
            if (str_contains($rel, '/api/')) $hasApi = true;
            if (str_contains($rel, '/web/')) $hasWeb = true;
        }

        return ($hasApi && $hasWeb) || ($hasApi && count($controllers) > 1);
    }

    protected function parseExplicitRoutes(): array
    {
        $routesFile = $this->appPath . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php';
        if (!file_exists($routesFile)) return [];

        $lines = file($routesFile, FILE_IGNORE_NEW_LINES);
        $routes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/\$routes->(get|post|put|delete|options|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/i', $line, $m)) {
                $routes[] = [
                    'http_method' => strtoupper($m[1]),
                    'uri'         => $m[2],
                    'handler'     => $m[3],
                ];
            }
        }

        return $routes;
    }

    protected function buildModuleRouteInventory(array $explicitRoutes, array $controllersInfo): array
    {
        $inventory = [];
        $coveredMethods = []; // ['ControllerName::methodName' => true]

        // 1. Petakan rute eksplisit terlebih dahulu
        foreach ($controllersInfo as $c) {
            $cBase = pathinfo($c['filename'], PATHINFO_FILENAME);
            $isModern = str_contains($c['relative_path'], '/Api/') || str_contains($c['relative_path'], '/Web/');

            foreach ($explicitRoutes as $er) {
                $handler = $er['handler'];
                if (stripos($handler, $cBase) !== false) {
                    $handlerParts = explode('::', $handler);
                    $methodName = isset($handlerParts[1]) ? explode('/', $handlerParts[1])[0] : 'index';
                    $coveredMethods[strtolower($cBase . '::' . $methodName)] = true;

                    // Tentukan status migrasi
                    $status = $isModern ? '🟢 MIGRATED' : '🟡 LEGACY';
                    $note   = $isModern ? 'Endpoint terstruktur di Api/Web' : 'Rute eksplisit ke flat controller lama';

                    $inventory[] = [
                        'http_method' => $er['http_method'],
                        'uri'         => $er['uri'],
                        'type'        => 'Explicit',
                        'handler'     => $handler,
                        'status'      => $status,
                        'notes'       => $note,
                    ];
                }
            }
        }

        // 2. Petakan AutoRoutes untuk setiap public method controller yang BELUM punya rute eksplisit
        foreach ($controllersInfo as $c) {
            $cBase = pathinfo($c['filename'], PATHINFO_FILENAME);
            $isModern = str_contains($c['relative_path'], '/Api/') || str_contains($c['relative_path'], '/Web/');

            // AutoRoute CI4 defaultnya memetakan controller non-namespaced ke /controller/method
            $cSlug = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $cBase));

            foreach ($c['public_methods'] as $mSig) {
                $mName = explode('(', $mSig)[0];
                $key = strtolower($cBase . '::' . $mName);

                if (!isset($coveredMethods[$key])) {
                    $coveredMethods[$key] = true;

                    $status = $isModern ? '🟢 MIGRATED' : '🟡 LEGACY (AutoRoute)';
                    $note   = $isModern ? 'Rute method aktif' : 'Implisit via AutoRoute convention (belum didaftarkan eksplisit)';

                    $inventory[] = [
                        'http_method' => 'ANY',
                        'uri'         => '/' . $cSlug . '/' . strtolower($mName),
                        'type'        => 'AutoRoute',
                        'handler'     => $cBase . '::' . $mName,
                        'status'      => $status,
                        'notes'       => $note,
                    ];
                }
            }
        }

        return $inventory;
    }

    protected function findUnmappedFiles(array $mappedFiles): array
    {
        $unmapped = [
            'controllers' => [],
            'services'    => [],
            'repositories'=> [],
            'models'      => [],
        ];

        $layers = [
            'controllers'  => 'Controllers',
            'services'     => 'Services',
            'repositories' => 'Repositories',
            'models'       => 'Models',
        ];

        foreach ($layers as $key => $folder) {
            $dir = $this->appPath . $folder;
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $fullPath = $file->getPathname();
                    $rel = 'app/' . $folder . '/' . str_replace('\\', '/', substr($fullPath, strlen($dir) + 1));
                    
                    // Kecualikan file base/framework/internal
                    if (basename($rel) === 'BaseController.php' || str_contains($rel, 'Refactor')) {
                        continue;
                    }

                    if (!in_array($rel, $mappedFiles[$key])) {
                        $unmapped[$key][] = $rel;
                    }
                }
            }
        }

        return $unmapped;
    }
}
