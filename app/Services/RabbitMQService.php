<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    protected $exchange = "amq.direct";
    protected $queue = "trackingstatus";
    protected $routingKey = "mykey";

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST'),
            false,
            'AMQPLAIN',
            null,
            'en_US',
            30, //Connection Timeout
            30 // Read/Write Timeout
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($this->exchange, 'direct', false, true,false);
        $this->channel->queue_declare($this->queue, false, true, false);
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);
    }

    public function connect(){
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST'),
            false,
            'AMQPLAIN',
            null,
            'en_US',
            30, //Connection Timeout
            30 // Read/Write Timeout
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($this->exchange, 'direct', true, true, true);
        $this->channel->queue_declare($this->queue, false, true, true, true);
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);

    }

    public function publish($message)
    {
        //$this->connect();
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, $this->exchange, $this->routingKey);
        // $this->channel->close();
       // $this->connection->close();
    }



    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
