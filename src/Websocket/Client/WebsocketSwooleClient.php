<?php namespace Vankosoft\WebsocketBundle\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;

// use Swoole\Http\Client as SwooleClient;
use Swoole\Coroutine\Http\Client as SwooleClient;
use Swoole\WebSocket\Server as WebsocketServer;
use Swoole\WebSocket\Frame as SwooleFrame;
use Swoole\Http\Status as SwooleHttpStatus;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Vankosoft\WebsocketBundle\Websocket\Message\Frame;

/**
 * Manual: https://github.com/lessmore92/swoole-websocket-client/blob/main/src/WebSocketClient.php
 */
final class WebsocketSwooleClient extends AbstractWebsocketClient
{
    /** @var WebsocketServer */
    private $connection;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        string $websocketUrl,
        WebsocketServer $connection
    ) {
        parent::__construct( $logger, $serializer, $websocketUrl );
        
        $this->connection   = $connection;
    }
    
    public function send( object $msg ): void
    {
        $json   = $this->serializer->serialize( $msg, JsonEncoder::FORMAT );
        
        foreach ( $this->connection->connections as $fd ) {
            if ( $this->connection->isEstablished( $fd ) ) {
                $this->connection->push( $fd, $json );
            }
        }
    }
    
    /**
     * Разликата между disconnect() и close()
     * =======================================
     * • $server->disconnect($fd, $code, $reason): Провежда правилната процедура по затваряне на WebSocket (Websocket handshake close). Браузърът ще прихване събитието в ws.onclose с подадения от вас код и съобщение.
     * • $server->close($fd): Грубо затваряне на TCP сокета на по-ниско ниво. Клиентът просто ще отчете, че връзката е прекъсната (често с код 1006 - Abnormal Closure), без да разбере конкретната причина.
     *
     */
    public function close( int $code ): void
    {
        foreach ( $this->connection->connections as $fd ) {
            if ( $this->connection->isEstablished( $fd ) ) {
                // Примерно условие: ако клиентът изпрати "kickme", го изключваме
                if ( $code === Frame::CLOSE_NORMAL ) {
                    // 1000 е стандартен статус код за нормално затваряне (Normal Closure)
                    $this->connection->disconnect( $fd, $code, "Ти поиска да бъдеш изключен." );
                    return;
                }
            }
        }
    }
}
