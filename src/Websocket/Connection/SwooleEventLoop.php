<?php

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * I Want to Use This Insteed of React Event Loop
 */
class SwooleEventLoop
{
    public static function run()
    {
        Coroutine\run(function () {
            // Създаваме буфер (канал) с капацитет за 10 съобщения
            $buffer = new Channel( 10 );
            
            // Корутина-производител (Пълни буфера)
            Coroutine::create( function () use ( $buffer ) {
                for ( $i = 0; $i < 5; $i++ ) {
                    $buffer->push( "Съобщение #{$i}" );
                    Coroutine::sleep( 0.5 );
                }
            });
                
            // Корутина-консуматор (Чете от буфера)
            Coroutine::create( function () use ( $buffer ) {
                while ( true ) {
                    $data = $buffer->pop(); // Изчаква съобщение, ако буферът е празен
                    if ( $data === false ) break;
                    echo "Получено: " . $data . PHP_EOL;
                }
            });
        });
    }
}