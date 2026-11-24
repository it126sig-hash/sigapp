<?php

namespace App\Services\Copilot;

class GeminiProvider implements LlmProviderInterface
{
    /**
     * @var callable(string, string, array, array, int): array
     */
    private $httpTransport;

    /**
     * @param ?callable(string, string, array, array, int): array $httpTransport
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta',
        private readonly int $timeout = 20,
        ?callable $httpTransport = null,
    ) {
        $this->httpTransport = $httpTransport ?? [$this, 'defaultTransport'];
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
    {
        if (trim($this->apiKey) === '') {
            throw new LlmProviderException('API key Gemini belum dikonfigurasi.', 400);
        }

        $timeout = min(20, max(1, (int) ($options['timeout'] ?? $this->timeout)));
        $url = rtrim($this->baseUrl, '/') . '/models/' . rawurlencode($this->model) . ':generateContent';
        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type'   => 'application/json',
        ];

        $payload = $this->buildPayload($messages, $tools, $options);

        try {
            $response = ($this->httpTransport)('POST', $url, $headers, $payload, $timeout);
        } catch (LlmProviderException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LlmProviderException('Gagal menghubungi Gemini API: ' . $e->getMessage(), 502, $e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function buildPayload(array $messages, array $tools, array $options): array
    {
        $systemTexts = [];
        $contents = [];

        foreach ($messages as $message) {
            $role = strtolower((string) ($message['role'] ?? 'user'));

            if ($role === 'system') {
                $text = trim((string) ($message['content'] ?? ''));
                if ($text !== '') {
                    $systemTexts[] = $text;
                }
                continue;
            }

            if ($role === 'assistant' || $role === 'model') {
                $parts = $this->buildModelParts($message);
                if ($parts !== []) {
                    $contents[] = [
                        'role'  => 'model',
                        'parts' => $parts,
                    ];
                }
                continue;
            }

            if ($role === 'function' || $role === 'tool') {
                $toolName = (string) ($message['name'] ?? $message['tool_name'] ?? '');
                $responseObj = $this->normalizeFunctionResponse($message['response'] ?? ($message['content'] ?? []));
                $contents[] = [
                    'role'  => 'function',
                    'parts' => [[
                        'functionResponse' => [
                            'name'     => $toolName,
                            'response' => $responseObj,
                        ],
                    ]],
                ];
                continue;
            }

            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => (string) ($message['content'] ?? '')]],
            ];
        }

        $payload = ['contents' => $contents];

        if ($systemTexts !== []) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => implode("\n\n", $systemTexts)]],
            ];
        }

        if ($tools !== []) {
            $declarations = [];
            foreach ($tools as $tool) {
                $fn = isset($tool['function']) && is_array($tool['function']) ? $tool['function'] : $tool;
                $decl = [
                    'name'        => (string) ($fn['name'] ?? ''),
                    'description' => (string) ($fn['description'] ?? ''),
                ];
                if (! empty($fn['parameters']) && is_array($fn['parameters'])) {
                    $params = $fn['parameters'];
                    if (isset($params['properties']) && $params['properties'] === []) {
                        $params['properties'] = (object) [];
                    }
                    $decl['parameters'] = $params;
                }
                $declarations[] = $decl;
            }
            $payload['tools'] = [['functionDeclarations' => $declarations]];
        }

        $generationConfig = [];
        if (isset($options['temperature'])) {
            $generationConfig['temperature'] = (float) $options['temperature'];
        }
        if (isset($options['maxOutputTokens']) || isset($options['max_tokens'])) {
            $generationConfig['maxOutputTokens'] = (int) ($options['maxOutputTokens'] ?? $options['max_tokens']);
        }
        if ($generationConfig !== []) {
            $payload['generationConfig'] = $generationConfig;
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $message
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildModelParts(array $message): array
    {
        if (! empty($message['rawParts']) && is_array($message['rawParts'])) {
            return $message['rawParts'];
        }
        if (! empty($message['rawMessage']['parts']) && is_array($message['rawMessage']['parts'])) {
            return $message['rawMessage']['parts'];
        }

        $parts = [];
        $text = (string) ($message['content'] ?? '');
        if ($text !== '') {
            $parts[] = ['text' => $text];
        }

        if (! empty($message['toolCalls']) && is_array($message['toolCalls'])) {
            foreach ($message['toolCalls'] as $tc) {
                $args = $tc['arguments'] ?? [];
                $parts[] = [
                    'functionCall' => [
                        'name' => (string) ($tc['name'] ?? ''),
                        'args' => $args === [] ? (object) [] : $args,
                    ],
                ];
            }
        }

        return $parts;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFunctionResponse(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_is_list($raw) ? ['items' => $raw] : $raw;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_is_list($decoded) ? ['items' => $decoded] : $decoded;
            }

            return ['result' => $raw];
        }

        return ['result' => $raw];
    }

    /**
     * @param array<string, mixed> $response
     */
    private function parseResponse(array $response): LlmResponse
    {
        $status = (int) ($response['status'] ?? 0);
        $rawBody = $response['body'] ?? '';
        $decoded = is_array($rawBody) ? $rawBody : json_decode((string) $rawBody, true);

        if ($status >= 400 || ! is_array($decoded)) {
            $errMsg = is_array($decoded)
                ? (string) ($decoded['error']['message'] ?? "HTTP {$status}")
                : "HTTP {$status} dengan respons tidak valid";
            throw new LlmProviderException('Gemini API error: ' . $errMsg, $status >= 400 ? $status : 502);
        }

        $candidate = $decoded['candidates'][0] ?? null;
        if (! is_array($candidate)) {
            $blockReason = (string) ($decoded['promptFeedback']['blockReason'] ?? 'tidak ada kandidat respons');
            throw new LlmProviderException('Gemini tidak mengembalikan respons: ' . $blockReason, 502);
        }

        $parts = is_array($candidate['content']['parts'] ?? null) ? $candidate['content']['parts'] : [];
        $textParts = [];
        $toolCalls = [];

        foreach ($parts as $idx => $part) {
            if (! is_array($part)) {
                continue;
            }
            if (isset($part['text']) && empty($part['thought'])) {
                $textParts[] = (string) $part['text'];
            }
            if (isset($part['functionCall']) && is_array($part['functionCall'])) {
                $fc = $part['functionCall'];
                $args = is_array($fc['args'] ?? null) ? $fc['args'] : [];
                $toolCalls[] = [
                    'id'        => (string) ($fc['id'] ?? ('gemini_call_' . $idx)),
                    'name'      => (string) ($fc['name'] ?? ''),
                    'arguments' => $args,
                ];
            }
        }

        return new LlmResponse(
            text: trim(implode('', $textParts)),
            toolCalls: $toolCalls,
            rawMessage: [
                'role'  => 'model',
                'parts' => $parts,
            ],
            finishReason: (string) ($candidate['finishReason'] ?? 'STOP'),
            usage: is_array($decoded['usageMetadata'] ?? null) ? $decoded['usageMetadata'] : [],
        );
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $body
     *
     * @return array{status: int, body: string}
     */
    private function defaultTransport(string $method, string $url, array $headers, array $body, int $timeout): array
    {
        $client = \Config\Services::curlrequest([
            'timeout'     => $timeout,
            'http_errors' => false,
        ]);

        $response = $client->request($method, $url, [
            'headers' => $headers,
            'json'    => $body,
        ]);

        return [
            'status' => (int) $response->getStatusCode(),
            'body'   => (string) $response->getBody(),
        ];
    }
}
