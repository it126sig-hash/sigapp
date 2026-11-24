<?php

namespace App\Services\Copilot;

class OpenAiCompatibleProvider implements LlmProviderInterface
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
        private readonly string $baseUrl = 'https://api.openai.com/v1',
        private readonly int $timeout = 20,
        ?callable $httpTransport = null,
        private readonly string $providerName = 'openai',
    ) {
        $this->httpTransport = $httpTransport ?? [$this, 'defaultTransport'];
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
    {
        if (trim($this->apiKey) === '') {
            throw new LlmProviderException("API key untuk provider '{$this->providerName}' belum dikonfigurasi.", 400);
        }
        if (trim($this->baseUrl) === '') {
            throw new LlmProviderException("Base URL untuk provider '{$this->providerName}' belum dikonfigurasi.", 400);
        }

        $timeout = min(20, max(1, (int) ($options['timeout'] ?? $this->timeout)));
        $url = rtrim($this->baseUrl, '/') . '/chat/completions';
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ];

        $payload = $this->buildPayload($messages, $tools, $options);

        try {
            $response = ($this->httpTransport)('POST', $url, $headers, $payload, $timeout);
        } catch (LlmProviderException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LlmProviderException("Gagal menghubungi {$this->providerName} API: " . $e->getMessage(), 502, $e);
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
        $normalizedMessages = [];

        foreach ($messages as $message) {
            $role = strtolower((string) ($message['role'] ?? 'user'));

            if ($role === 'assistant' || $role === 'model') {
                if (! empty($message['rawMessage']) && is_array($message['rawMessage'])) {
                    $raw = $message['rawMessage'];
                    $raw['role'] = 'assistant';
                    $normalizedMessages[] = $raw;
                    continue;
                }

                $entry = [
                    'role'    => 'assistant',
                    'content' => $message['content'] ?? null,
                ];

                if (! empty($message['toolCalls']) && is_array($message['toolCalls'])) {
                    $entry['tool_calls'] = array_map(static function (array $tc, int $idx): array {
                        $args = $tc['arguments'] ?? [];
                        return [
                            'id'       => (string) ($tc['id'] ?? ('call_' . $idx)),
                            'type'     => 'function',
                            'function' => [
                                'name'      => (string) ($tc['name'] ?? ''),
                                'arguments' => json_encode($args === [] ? (object) [] : $args, JSON_UNESCAPED_UNICODE),
                            ],
                        ];
                    }, $message['toolCalls'], array_keys($message['toolCalls']));
                }

                $normalizedMessages[] = $entry;
                continue;
            }

            if ($role === 'tool' || $role === 'function') {
                $content = $message['content'] ?? ($message['response'] ?? '');
                if (! is_string($content)) {
                    $content = (string) json_encode($content, JSON_UNESCAPED_UNICODE);
                }

                $entry = [
                    'role'         => 'tool',
                    'tool_call_id' => (string) ($message['tool_call_id'] ?? $message['id'] ?? ''),
                    'content'      => $content,
                ];
                $normalizedMessages[] = $entry;
                continue;
            }

            $normalizedMessages[] = [
                'role'    => $role === 'system' ? 'system' : 'user',
                'content' => (string) ($message['content'] ?? ''),
            ];
        }

        $payload = [
            'model'    => $this->model,
            'messages' => $normalizedMessages,
        ];

        if ($tools !== []) {
            $normalizedTools = [];
            foreach ($tools as $tool) {
                $fn = isset($tool['function']) && is_array($tool['function']) ? $tool['function'] : $tool;
                $params = $fn['parameters'] ?? ['type' => 'object', 'properties' => (object) []];
                if (is_array($params) && isset($params['properties']) && $params['properties'] === []) {
                    $params['properties'] = (object) [];
                }

                $normalizedTools[] = [
                    'type'     => 'function',
                    'function' => [
                        'name'        => (string) ($fn['name'] ?? ''),
                        'description' => (string) ($fn['description'] ?? ''),
                        'parameters'  => $params,
                    ],
                ];
            }

            $payload['tools'] = $normalizedTools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        if (isset($options['temperature'])) {
            $payload['temperature'] = (float) $options['temperature'];
        }
        if (isset($options['max_tokens']) || isset($options['maxOutputTokens'])) {
            $payload['max_tokens'] = (int) ($options['max_tokens'] ?? $options['maxOutputTokens']);
        }

        return $payload;
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
                ? (string) ($decoded['error']['message'] ?? ($decoded['message'] ?? "HTTP {$status}"))
                : "HTTP {$status} dengan respons tidak valid";
            throw new LlmProviderException("{$this->providerName} API error: {$errMsg}", $status >= 400 ? $status : 502);
        }

        $choice = $decoded['choices'][0] ?? null;
        if (! is_array($choice) || ! is_array($choice['message'] ?? null)) {
            throw new LlmProviderException("{$this->providerName} tidak mengembalikan pilihan respons.", 502);
        }

        $message = $choice['message'];
        $toolCalls = [];

        if (! empty($message['tool_calls']) && is_array($message['tool_calls'])) {
            foreach ($message['tool_calls'] as $idx => $tc) {
                if (! is_array($tc)) {
                    continue;
                }
                $fn = is_array($tc['function'] ?? null) ? $tc['function'] : [];
                $rawArgs = $fn['arguments'] ?? '{}';
                if (is_array($rawArgs)) {
                    $args = $rawArgs;
                } else {
                    $parsedArgs = json_decode((string) $rawArgs, true);
                    $args = is_array($parsedArgs) ? $parsedArgs : [];
                }

                $toolCalls[] = [
                    'id'        => (string) ($tc['id'] ?? ('call_' . $idx)),
                    'name'      => (string) ($fn['name'] ?? ''),
                    'arguments' => $args,
                ];
            }
        }

        return new LlmResponse(
            text: trim((string) ($message['content'] ?? '')),
            toolCalls: $toolCalls,
            rawMessage: $message,
            finishReason: (string) ($choice['finish_reason'] ?? 'stop'),
            usage: is_array($decoded['usage'] ?? null) ? $decoded['usage'] : [],
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
