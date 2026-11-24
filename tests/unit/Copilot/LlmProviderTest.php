<?php

use App\Repositories\Copilot\SettingRepository;
use App\Services\Copilot\GeminiProvider;
use App\Services\Copilot\LlmProviderException;
use App\Services\Copilot\LlmProviderFactory;
use App\Services\Copilot\OpenAiCompatibleProvider;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Copilot;

final class LlmProviderTest extends CIUnitTestCase
{
    public function testGeminiProviderFormatsPayloadAndParsesFunctionCallAndText(): void
    {
        $captured = null;

        $provider = new GeminiProvider(
            apiKey: 'gem-secret-key',
            model: 'gemini-flash-latest',
            baseUrl: 'https://generativelanguage.googleapis.com/v1beta',
            timeout: 20,
            httpTransport: static function (string $method, string $url, array $headers, array $body, int $timeout) use (&$captured): array {
                $captured = compact('method', 'url', 'headers', 'body', 'timeout');

                return [
                    'status' => 200,
                    'body'   => json_encode([
                        'candidates' => [[
                            'content' => [
                                'role'  => 'model',
                                'parts' => [
                                    ['text' => 'Saya cek dulu datanya. '],
                                    [
                                        'functionCall' => [
                                            'name' => 'get_booking_per_bulan',
                                            'args' => ['tahun' => 2026, 'bulan' => 11],
                                        ],
                                        'thoughtSignature' => 'sig-xyz-123',
                                    ],
                                ],
                            ],
                            'finishReason' => 'STOP',
                        ]],
                        'usageMetadata' => [
                            'promptTokenCount'     => 120,
                            'candidatesTokenCount' => 35,
                        ],
                    ]),
                ];
            }
        );

        $messages = [
            ['role' => 'system', 'content' => 'Anda asisten SIGAPP.'],
            ['role' => 'user', 'content' => 'Berapa booking bulan ini?'],
            [
                'role'     => 'model',
                'rawParts' => [[
                    'functionCall'     => ['name' => 'get_booking_per_bulan', 'args' => ['tahun' => 2026]],
                    'thoughtSignature' => 'sig-prev',
                ]],
            ],
            [
                'role'     => 'function',
                'name'     => 'get_booking_per_bulan',
                'response' => ['total' => 12],
            ],
        ];

        $tools = [[
            'type'     => 'function',
            'function' => [
                'name'        => 'get_booking_per_bulan',
                'description' => 'Ambil total booking per bulan',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'tahun' => ['type' => 'integer'],
                        'bulan' => ['type' => 'integer'],
                    ],
                ],
            ],
        ]];

        $response = $provider->chat($messages, $tools);

        $this->assertNotNull($captured);
        $this->assertSame('POST', $captured['method']);
        $this->assertSame(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent',
            $captured['url']
        );
        $this->assertSame('gem-secret-key', $captured['headers']['x-goog-api-key']);
        $this->assertSame(20, $captured['timeout']);

        // Verify systemInstruction, contents (user, model rawParts, functionResponse), and tools
        $this->assertSame('Anda asisten SIGAPP.', $captured['body']['systemInstruction']['parts'][0]['text']);
        $this->assertCount(3, $captured['body']['contents']);
        $this->assertSame('user', $captured['body']['contents'][0]['role']);
        $this->assertSame('model', $captured['body']['contents'][1]['role']);
        $this->assertSame('sig-prev', $captured['body']['contents'][1]['parts'][0]['thoughtSignature']);
        $this->assertSame('function', $captured['body']['contents'][2]['role']);
        $this->assertSame(
            ['name' => 'get_booking_per_bulan', 'response' => ['total' => 12]],
            $captured['body']['contents'][2]['parts'][0]['functionResponse']
        );
        $this->assertSame(
            'get_booking_per_bulan',
            $captured['body']['tools'][0]['functionDeclarations'][0]['name']
        );

        // Verify parsed LlmResponse
        $this->assertSame('Saya cek dulu datanya.', $response->text);
        $this->assertTrue($response->hasToolCalls());
        $this->assertCount(1, $response->toolCalls);
        $this->assertSame('get_booking_per_bulan', $response->toolCalls[0]['name']);
        $this->assertSame(['tahun' => 2026, 'bulan' => 11], $response->toolCalls[0]['arguments']);
        $this->assertSame('sig-xyz-123', $response->rawMessage['parts'][1]['thoughtSignature']);
        $this->assertSame(120, $response->usage['promptTokenCount']);
    }

    public function testGeminiProviderThrowsExceptionOnErrorResponse(): void
    {
        $provider = new GeminiProvider(
            apiKey: 'gem-secret-key',
            model: 'gemini-flash-latest',
            httpTransport: static fn (): array => [
                'status' => 401,
                'body'   => json_encode(['error' => ['message' => 'API key not valid']]),
            ]
        );

        $this->expectException(LlmProviderException::class);
        $this->expectExceptionMessage('API key not valid');
        $provider->chat([['role' => 'user', 'content' => 'Halo']]);
    }

    public function testOpenAiCompatibleProviderFormatsPayloadAndParsesToolCallsAndContent(): void
    {
        $captured = null;

        $provider = new OpenAiCompatibleProvider(
            apiKey: 'oa-secret-key',
            model: 'gpt-4o-mini',
            baseUrl: 'https://api.openai.com/v1',
            timeout: 15,
            httpTransport: static function (string $method, string $url, array $headers, array $body, int $timeout) use (&$captured): array {
                $captured = compact('method', 'url', 'headers', 'body', 'timeout');

                return [
                    'status' => 200,
                    'body'   => json_encode([
                        'choices' => [[
                            'message' => [
                                'role'       => 'assistant',
                                'content'    => 'Berikut datanya.',
                                'tool_calls' => [[
                                    'id'       => 'call_abc',
                                    'type'     => 'function',
                                    'function' => [
                                        'name'      => 'get_jatuh_tempo_cicilan',
                                        'arguments' => '{"days":7}',
                                    ],
                                ]],
                            ],
                            'finish_reason' => 'tool_calls',
                        ]],
                        'usage' => ['total_tokens' => 88],
                    ]),
                ];
            },
            providerName: 'openai'
        );

        $messages = [
            ['role' => 'system', 'content' => 'System prompt'],
            ['role' => 'user', 'content' => 'Cek cicilan jatuh tempo'],
            [
                'role'       => 'assistant',
                'rawMessage' => [
                    'role'       => 'assistant',
                    'content'    => null,
                    'tool_calls' => [[
                        'id'       => 'call_prev',
                        'type'     => 'function',
                        'function' => ['name' => 'get_jatuh_tempo_cicilan', 'arguments' => '{"days":7}'],
                    ]],
                ],
            ],
            [
                'role'         => 'tool',
                'tool_call_id' => 'call_prev',
                'content'      => '{"rows":3}',
            ],
        ];

        $tools = [[
            'type'     => 'function',
            'function' => [
                'name'        => 'get_jatuh_tempo_cicilan',
                'description' => 'Ambil cicilan jatuh tempo',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['days' => ['type' => 'integer']],
                ],
            ],
        ]];

        $response = $provider->chat($messages, $tools);

        $this->assertNotNull($captured);
        $this->assertSame('POST', $captured['method']);
        $this->assertSame('https://api.openai.com/v1/chat/completions', $captured['url']);
        $this->assertSame('Bearer oa-secret-key', $captured['headers']['Authorization']);
        $this->assertSame('gpt-4o-mini', $captured['body']['model']);
        $this->assertSame('tool', $captured['body']['messages'][3]['role']);
        $this->assertSame('call_prev', $captured['body']['messages'][3]['tool_call_id']);
        $this->assertSame('auto', $captured['body']['tool_choice']);
        $this->assertSame('get_jatuh_tempo_cicilan', $captured['body']['tools'][0]['function']['name']);

        $this->assertSame('Berikut datanya.', $response->text);
        $this->assertTrue($response->hasToolCalls());
        $this->assertSame('call_abc', $response->toolCalls[0]['id']);
        $this->assertSame('get_jatuh_tempo_cicilan', $response->toolCalls[0]['name']);
        $this->assertSame(['days' => 7], $response->toolCalls[0]['arguments']);
        $this->assertSame('tool_calls', $response->finishReason);
        $this->assertSame(88, $response->usage['total_tokens']);
    }

    public function testFactorySwitchesProviderFromDbSetting(): void
    {
        $config = new Copilot();
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo->method('getAll')->willReturn([
            'provider' => 'groq',
            'model'    => 'llama-3.1-8b-instant',
            'base_url' => 'https://api.groq.com/openai/v1',
        ]);

        $factory = new LlmProviderFactory(
            config: $config,
            settingRepository: $settingRepo,
            envResolver: static fn (string $key): string => $key === 'GROQ_API_KEY' ? 'groq-test-key' : '',
        );

        $provider = $factory->make();

        $this->assertInstanceOf(OpenAiCompatibleProvider::class, $provider);
        $this->assertSame('groq', $provider->getProviderName());
        $this->assertSame('llama-3.1-8b-instant', $provider->getModelName());
    }

    public function testFactoryThrowsInformativeErrorWhenApiKeyMissing(): void
    {
        $config = new Copilot();
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo->method('getAll')->willReturn(['provider' => 'gemini']);

        $factory = new LlmProviderFactory(
            config: $config,
            settingRepository: $settingRepo,
            envResolver: static fn (): string => '',
        );

        $this->expectException(LlmProviderException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY');
        $factory->make();
    }

    public function testFactoryThrowsWhenProviderUnknown(): void
    {
        $config = new Copilot();
        $settingRepo = $this->createMock(SettingRepository::class);
        $settingRepo->method('getAll')->willReturn(['provider' => 'anthropic']);

        $factory = new LlmProviderFactory(
            config: $config,
            settingRepository: $settingRepo,
            envResolver: static fn (): string => 'some-key',
        );

        $this->expectException(LlmProviderException::class);
        $this->expectExceptionMessage("Provider LLM 'anthropic' tidak dikenali.");
        $factory->make();
    }
}
