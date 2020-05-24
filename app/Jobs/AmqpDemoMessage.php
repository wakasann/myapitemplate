<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/24 0024
 * Time: 12:13
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 連接RabbitMQ的 queue
 * Class AmqpDemoMessage
 * @package App\Jobs
 */
class AmqpDemoMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $host = '192.168.1.101';
    protected $port = 5672;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     * @link https://github.com/php-amqplib/php-amqplib
     * @return void
     */
    public function handle()
    {
        $exchange = 'amq.topic';
        $queue = 'order_queue';
        //HOST, PORT, USER, PASS, VHOST
        $connection = new AMQPStreamConnection($this->host, $this->port, 'demo', '123456','/');
        $channel = $connection->channel();

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, true);
        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($exchange, 'topic', false, true, false);
        $channel->queue_bind($queue, $exchange,$queue);
        $data = array(
            'order_id' => 1,
        );
        $msg = new AMQPMessage(json_encode($data));
        $channel->basic_publish($msg, $exchange, $queue);
        $channel->close();
        $connection->close();
    }
}