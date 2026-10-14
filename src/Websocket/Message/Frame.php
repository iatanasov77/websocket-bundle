<?php namespace Vankosoft\WebsocketBundle\Websocket\Message;

/*
 * Inspire from Ratchet\RFC6455\Messaging\Frame
 * ==============================================
 * But I Get Only Close Constants
 */
class Frame
{
    const CLOSE_NORMAL      = 1000;
    const CLOSE_GOING_AWAY  = 1001;
    const CLOSE_PROTOCOL    = 1002;
    const CLOSE_BAD_DATA    = 1003;
    const CLOSE_NO_STATUS   = 1005;
    const CLOSE_ABNORMAL    = 1006;
    const CLOSE_BAD_PAYLOAD = 1007;
    const CLOSE_POLICY      = 1008;
    const CLOSE_TOO_BIG     = 1009;
    const CLOSE_MAND_EXT    = 1010;
    const CLOSE_SRV_ERR     = 1011;
    const CLOSE_TLS         = 1015;
}