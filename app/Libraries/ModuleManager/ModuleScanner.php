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
        $catalog        = ModuleCatalog::getModules();
        $sharedInfra    = ModuleCatalog::getSharedInfrastructure();
        $explicitRoutes = $this->parseExplicitRoutes();
        $mappedFiles    = [
            'controllers'  => $sharedInfra['controllers'] ?? [],
            'services'     => $sharedInfra['services'] ?? [],
            'repositories' => $sharedInfra['repositories'] ?? [],
            'models'       => $sharedInfra['models'] ?? [],
        ];

        $modulesResult = [];

        foreach ($catalog as $key => $meta) {
            $controllersInfo  = $this->resolveFiles('Controllers', $meta['controllers'] ?? []);
            $servicesInfo     = $this->resolveFiles('Services', $meta['services'] ?? []);
            $repositoriesInfo = $this->resolveFiles('Repositories', $meta['repositories'] ?? []);
            $modelsInfo       = $this->resolveFiles('Models', $meta['models'] ?? []);
            $shimsInfo        = $this->resolveShims($meta['shims'] ?? []);
            $viewsInfo        = $this->resolveViews($meta['views'] ?? []);
            $assetsJsInfo     = $this->resolveAssets($meta['assets_js'] ?? []);

            // Catat file yang sudah terpetakan
            foreach ($controllersInfo as $c)  { $mappedFiles['controllers'][]  = $c['relative_path']; }
            foreach ($servicesInfo as $s)     { $mappedFiles['services'][]     = $s['relative_path']; }
            foreach ($repositoriesInfo as $r) { $mappedFiles['repositories'][] = $r['relative_path']; }
            foreach ($modelsInfo as $m)       { $mappedFiles['models'][]       = $m['relative_path']; }

            foreach ($shimsInfo as $shimPath) {
                if (str_starts_with($shimPath, 'app/Controllers/')) {
                    $mappedFiles['controllers'][] = $shimPath;
                } elseif (str_starts_with($shimPath, 'app/Services/')) {
                    $mappedFiles['services'][] = $shimPath;
                } elseif (str_starts_with($shimPath, 'app/Repositories/')) {
                    $mappedFiles['repositories'][] = $shimPath;
                } elseif (str_starts_with($shimPath, 'app/Models/')) {
                    $mappedFiles['models'][] = $shimPath;
                }
            }

            // Bangun inventori route lengkap (Explicit + AutoRoute)
            $moduleRoutes = $this->buildModuleRouteInventory($explicitRoutes, $controllersInfo);

            // Kumpulkan pelanggaran layer di Controller
            $controllerViolations = [];
            foreach ($controllersInfo as $c) {
                if (!empty($c['violations'])) {
                    $controllerViolations[$c['relative_path']] = $c['violations'];
                }
            }

            // Cek status dokumentasi
            $docFile = $this->docsPath . $key . DIRECTORY_SEPARATOR . 'MODULE.md';
            $hasDoc  = file_exists($docFile);

            // Pemeriksaan kesiapan modular per layer (subfolder /<ModulKey>/)
            $hasModularControllers = $this->isLayerModular('Controllers', $key, $controllersInfo);
            $hasModularServices    = $this->isLayerModular('Services', $key, $servicesInfo);
            $hasModularRepos       = $this->isLayerModular('Repositories', $key, $repositoriesInfo);

            $explicitCount  = count(array_filter($moduleRoutes, static fn(array $r): bool => ($r['type'] ?? '') === 'Explicit'));
            $autoRouteCount = count(array_filter($moduleRoutes, static fn(array $r): bool => ($r['type'] ?? '') === 'AutoRoute'));

            $hasExplicitRoutesOnly   = ($explicitCount > 0 && $autoRouteCount === 0);
            $hasZeroViolations       = empty($controllerViolations);
            $hasCleanRoutesAndLayers = ($hasExplicitRoutesOnly && $hasZeroViolations);
            $hasWebApiSplit          = $this->checkWebApiSplit($controllersInfo);

            // Hitung skor kesiapan (0-100)
            $score = 0;
            if ($hasModularServices) {
                $score += 20;
            } elseif (count($servicesInfo) > 0) {
                $score += 10;
            }

            if ($hasModularRepos) {
                $score += 20;
            } elseif (count($repositoriesInfo) > 0) {
                $score += 10;
            }

            if ($hasModularControllers) {
                $score += 20;
            } elseif ($hasWebApiSplit) {
                $score += 10;
            }

            if ($hasCleanRoutesAndLayers) {
                $score += 20;
            } elseif ($explicitCount > 0) {
                $score += 10;
            }

            if ($hasDoc) {
                $score += 20;
            }

            // Status modul
            if ($score >= 80 && $hasModularControllers && $hasModularServices && $hasModularRepos) {
                $status = 'REFACTORED';
            } elseif ($hasDoc) {
                $status = 'MAPPED';
            } else {
                $status = 'UNMAPPED';
            }

            // Ekstrak public contracts (Service methods)
            $serviceContracts = $this->extractServiceContracts($servicesInfo);

            $modulesResult[$key] = [
                'name'                  => $meta['name'],
                'description'           => $meta['description'],
                'status'                => $status,
                'readiness_score'       => $score,
                'readiness_checklist'   => [
                    'has_service'                => $hasModularServices,
                    'has_repository'             => $hasModularRepos,
                    'has_web_api_split'          => $hasModularControllers,
                    'has_explicit_routes'        => $hasExplicitRoutesOnly,
                    'zero_controller_violations' => $hasZeroViolations,
                    'has_documentation'          => $hasDoc,
                ],
                'controller_violations' => $controllerViolations,
                'owner_tables'          => $meta['owner_tables'] ?? [],
                'shared_tables'         => $meta['shared_tables'] ?? [],
                'controllers'           => $controllersInfo,
                'services'              => $servicesInfo,
                'repositories'          => $repositoriesInfo,
                'models'                => $modelsInfo,
                'shims'                 => $shimsInfo,
                'views'                 => $viewsInfo,
                'assets_js'             => $assetsJsInfo,
                'routes'                => $moduleRoutes,
                'service_contracts'     => $serviceContracts,
                'doc_path'              => 'docs/modules/' . $key . '/MODULE.md',
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

                $item = [
                    'relative_path' => 'app/' . $layer . '/' . str_replace('\\', '/', $relPath),
                    'filename'      => basename($normRelPath),
                    'line_count'    => $lines,
                    'public_methods'=> $methods,
                ];

                if ($layer === 'Controllers') {
                    $item['violations'] = $this->detectControllerViolations($content);
                }

                $result[] = $item;
            }
        }

        return $result;
    }

    protected function resolveShims(array $shimList): array
    {
        $rootDir = dirname($this->appPath) . DIRECTORY_SEPARATOR;
        $result  = [];

        foreach ($shimList as $shimRel) {
            $norm = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $shimRel);
            if (file_exists($rootDir . $norm)) {
                $result[] = str_replace('\\', '/', $shimRel);
            }
        }

        return $result;
    }

    protected function isLayerModular(string $layer, string $moduleKey, array $filesInfo): bool
    {
        if (empty($filesInfo)) {
            return false;
        }

        $quotedKey = preg_quote($moduleKey, '#');
        if ($layer === 'Controllers') {
            $pattern = '#^app/Controllers/(?:Web|Api)/' . $quotedKey . '/#i';
        } else {
            $pattern = '#^app/' . preg_quote($layer, '#') . '/' . $quotedKey . '/#i';
        }

        $modularCount = 0;
        foreach ($filesInfo as $file) {
            if (preg_match($pattern, $file['relative_path'])) {
                $modularCount++;
            }
        }

        if ($layer === 'Controllers') {
            return $modularCount === count($filesInfo);
        }

        // Untuk Services dan Repositories, seluruh service/repository utama modul harus berada di subfolder <ModulKey>/
        return $modularCount > 0 && ($modularCount / count($filesInfo)) >= 0.75;
    }

    protected function detectControllerViolations(string $content): array
    {
        $cleanContent = $this->stripPhpComments($content);
        $violations   = [];

        if (preg_match('/\bdb_connect\s*\(/', $cleanContent)) {
            $violations[] = 'db_connect()';
        }
        if (preg_match('/Database::connect\s*\(/', $cleanContent)) {
            $violations[] = 'Database::connect()';
        }
        if (preg_match('/\$this->db->table\s*\(/', $cleanContent)) {
            $violations[] = '$this->db->table()';
        }
        if (preg_match_all('/\bnew\s+([A-Z][A-Za-z0-9_\\\\]*Model)\s*\(/', $cleanContent, $matches)) {
            foreach (array_unique($matches[1]) as $modelClass) {
                $violations[] = 'new ' . $modelClass . '()';
            }
        }
        if (preg_match_all('/(?:\$this->|\b)model\s*\(\s*[\'"]([^\'"]+)[\'"]/', $cleanContent, $matches)) {
            foreach (array_unique($matches[1]) as $modelName) {
                $violations[] = "model('{$modelName}')";
            }
        }

        return array_values(array_unique($violations));
    }

    protected function stripPhpComments(string $content): string
    {
        $clean = '';
        foreach (@token_get_all($content) as $token) {
            if (is_array($token)) {
                if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }
                $clean .= $token[1];
            } else {
                $clean .= $token;
            }
        }

        return $clean;
    }

    protected function resolveViews(array $dirList): array
    {
        $result = [];
        $baseViews = $this->appPath . 'Views' . DIRECTORY_SEPARATOR;

        foreach ($dirList as $dir) {
            $path = $baseViews . $dir;
            if (is_dir($path)) {
                $files = glob($path . DIRECTORY_SEPARATOR . '*.php');
                $fileNames = is_array($files) ? array_map('basename', $files) : [];
                $result[] = [
                    'folder'     => 'app/Views/' . $dir,
                    'file_count' => count($fileNames),
                    'files'      => $fileNames,
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
        $cleanContent = $this->stripPhpComments($content);
        $methods      = [];

        if (preg_match_all('/\b(public|protected|private)?\s*(?:static\s+)?function\s+([a-zA-Z0-9_]+)\s*\((.*?)\)/s', $cleanContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $visibility = strtolower(trim($m[1] ?? ''));
                if (in_array($visibility, ['protected', 'private'], true)) {
                    continue;
                }

                $name = $m[2];
                if (in_array($name, ['__construct', '__destruct', '__clone'], true)) {
                    continue;
                }

                $params    = trim(preg_replace('/\s+/', ' ', $m[3]));
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

        $rawContent   = file_get_contents($routesFile);
        $cleanContent = $this->stripPhpComments($rawContent);

        // Ratakan deklarasi $routes->group(...) multi-line menjadi satu baris hingga kurung kurawal pembuka {
        $cleanContent = preg_replace_callback(
            '/\$routes->group\s*\([^\{]+\{/s',
            static fn(array $m): string => preg_replace('/\s+/', ' ', $m[0]),
            $cleanContent
        );

        $lines      = preg_split('/\r\n|\r|\n/', $cleanContent);
        $routes     = [];
        $groupStack = [];
        $braceDepth = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/\$routes->group\s*\(\s*[\'"]([^\'"]*)[\'"]\s*,\s*(.*?)\{/i', $trimmed, $gm)) {
                $groupPrefix = trim($gm[1], '/');
                $groupOpts   = $gm[2];
                $groupNs     = '';

                if (preg_match('/[\'"]namespace[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/i', $groupOpts, $nsMatch)) {
                    $groupNs = trim(str_replace('\\\\', '\\', $nsMatch[1]), '\\');
                }

                $parentPrefix = !empty($groupStack) ? end($groupStack)['prefix'] : '';
                $parentNs     = !empty($groupStack) ? end($groupStack)['namespace'] : '';

                if ($parentPrefix !== '' && $groupPrefix !== '') {
                    $fullPrefix = $parentPrefix . '/' . $groupPrefix;
                } elseif ($parentPrefix !== '') {
                    $fullPrefix = $parentPrefix;
                } else {
                    $fullPrefix = $groupPrefix;
                }

                $effectiveNs = $groupNs !== '' ? $groupNs : $parentNs;

                $groupStack[] = [
                    'prefix'       => $fullPrefix,
                    'namespace'    => $effectiveNs,
                    'target_depth' => $braceDepth,
                ];
            } elseif (preg_match('/\$routes->(get|post|put|delete|options|patch)\s*\(\s*[\'"]([^\'"]*)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/i', $trimmed, $rm)) {
                $httpMethod = strtoupper($rm[1]);
                $routeUri   = trim($rm[2], '/');
                $handler    = trim(str_replace('\\\\', '\\', $rm[3]));

                $currentPrefix = !empty($groupStack) ? end($groupStack)['prefix'] : '';
                $currentNs     = !empty($groupStack) ? end($groupStack)['namespace'] : '';

                if ($currentPrefix !== '' && $routeUri !== '') {
                    $fullUri = '/' . $currentPrefix . '/' . $routeUri;
                } elseif ($currentPrefix !== '') {
                    $fullUri = '/' . $currentPrefix;
                } elseif ($routeUri !== '') {
                    $fullUri = '/' . $routeUri;
                } else {
                    $fullUri = '/';
                }

                $routes[] = [
                    'http_method' => $httpMethod,
                    'uri'         => $fullUri,
                    'handler'     => $handler,
                    'namespace'   => $currentNs,
                ];
            }

            $braceDepth += substr_count($trimmed, '{') - substr_count($trimmed, '}');
            while (!empty($groupStack) && $braceDepth <= end($groupStack)['target_depth']) {
                array_pop($groupStack);
            }
        }

        return $routes;
    }

    protected function buildModuleRouteInventory(array $explicitRoutes, array $controllersInfo): array
    {
        $inventory      = [];
        $seenRoutes     = [];
        $coveredMethods = []; // ['controller_rel_path::methodName' => true]

        // 1. Petakan rute eksplisit terlebih dahulu
        foreach ($controllersInfo as $c) {
            $cBase    = pathinfo($c['filename'], PATHINFO_FILENAME);
            $relPath  = $c['relative_path'];
            $isApi    = str_contains($relPath, '/Api/');
            $isWeb    = str_contains($relPath, '/Web/');
            $isModern = $isApi || $isWeb;

            foreach ($explicitRoutes as $er) {
                $handler      = $er['handler'];
                $handlerParts = explode('::', $handler);
                $handlerClass = trim($handlerParts[0], '\\');

                $classSegments     = explode('\\', $handlerClass);
                $handlerShortClass = end($classSegments);

                // Pencocokan nama class controller secara eksak (bukan substring)
                if (strcasecmp($handlerShortClass, $cBase) !== 0) {
                    continue;
                }

                // Tentukan full class dari handler + namespace grup
                if (str_starts_with($er['handler'], '\\') || stripos($handlerClass, 'App\\') === 0) {
                    $fullHandlerClass = $handlerClass;
                } elseif (!empty($er['namespace'])) {
                    $fullHandlerClass = trim($er['namespace'], '\\') . '\\' . $handlerClass;
                } else {
                    $fullHandlerClass = 'App\\Controllers\\' . $handlerClass;
                }

                $routeIsApi = stripos($fullHandlerClass, 'App\\Controllers\\Api\\') === 0
                    || stripos($fullHandlerClass, 'App\\Controllers\\Api') === 0
                    || str_starts_with(ltrim($er['uri'], '/'), 'api/');
                $routeIsWeb = stripos($fullHandlerClass, 'App\\Controllers\\Web\\') === 0
                    || stripos($fullHandlerClass, 'App\\Controllers\\Web') === 0;

                if ($isApi && !$routeIsApi) {
                    continue;
                }
                if ($isWeb && $routeIsApi) {
                    continue;
                }
                if (!$isModern && ($routeIsApi || $routeIsWeb)) {
                    continue;
                }

                $methodName = isset($handlerParts[1]) ? explode('/', $handlerParts[1])[0] : 'index';
                $coveredMethods[strtolower($relPath . '::' . $methodName)] = true;

                $dedupKey = strtoupper($er['http_method']) . '|' . $er['uri'] . '|' . $handler;
                if (isset($seenRoutes[$dedupKey])) {
                    continue;
                }
                $seenRoutes[$dedupKey] = true;

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

        // 2. Petakan AutoRoutes HANYA untuk flat controller legacy di luar /Api/ dan /Web/
        foreach ($controllersInfo as $c) {
            $relPath  = $c['relative_path'];
            $isModern = str_contains($relPath, '/Api/') || str_contains($relPath, '/Web/');
            if ($isModern) {
                continue;
            }

            $cBase = pathinfo($c['filename'], PATHINFO_FILENAME);
            $cSlug = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $cBase));

            foreach ($c['public_methods'] as $mSig) {
                $mName = explode('(', $mSig)[0];
                $key   = strtolower($relPath . '::' . $mName);

                if (!isset($coveredMethods[$key])) {
                    $coveredMethods[$key] = true;

                    $uri      = '/' . $cSlug . '/' . strtolower($mName);
                    $handler  = $cBase . '::' . $mName;
                    $dedupKey = 'ANY|' . $uri . '|' . $handler;

                    if (isset($seenRoutes[$dedupKey])) {
                        continue;
                    }
                    $seenRoutes[$dedupKey] = true;

                    $inventory[] = [
                        'http_method' => 'ANY',
                        'uri'         => $uri,
                        'type'        => 'AutoRoute',
                        'handler'     => $handler,
                        'status'      => '🟡 LEGACY (AutoRoute)',
                        'notes'       => 'Implisit via AutoRoute convention (belum didaftarkan eksplisit)',
                    ];
                }
            }
        }

        return $inventory;
    }

    protected function findUnmappedFiles(array $mappedFiles): array
    {
        $unmapped = [
            'controllers'  => [],
            'services'     => [],
            'repositories' => [],
            'models'       => [],
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

                    if (in_array($rel, $mappedFiles[$key], true)) {
                        continue;
                    }

                    // Fallback deteksi otomatis file Zero-Downtime Shim (class X extends \App\...)
                    $content = file_get_contents($fullPath);
                    $clean   = trim($this->stripPhpComments($content));
                    if (preg_match('/^<\?php\s+namespace\s+[^;]+;\s+class\s+[A-Za-z0-9_]+\s+extends\s+\\\\?App\\\\[A-Za-z0-9_\\\\]+\s*\{\s*\}$/s', $clean)) {
                        continue;
                    }

                    $unmapped[$key][] = $rel;
                }
            }
        }

        return $unmapped;
    }
}
