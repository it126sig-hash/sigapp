<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestPush extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:push';
    protected $description = 'Test web push';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $ws = new \App\Services\WebPushService();
        $subs = (new \App\Models\PushSubscriptionModel())->activeForUser(1);
        $res = $ws->sendToSubscriptions($subs, 'Test Title', 'Test Body', '/');
        CLI::write(print_r($res, true));
    }
}
