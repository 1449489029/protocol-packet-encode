<?php
namespace test;

use ProtocolPacketEncode\Protocol;
use ProtocolPacketEncode\ProtocolFormat;
use ProtocolPacketEncode\PacketInterface;
use ProtocolPacketEncode\BinaryPacket;


class LogicPkt extends Protocol
{
    public const FLAG_REQUEST = 1;
    public const FLAG_RESPONSE = 2;
    public const FLAG_PUSH = 3;

    // 代码类型
    public const COMMAND_LOGIN = 1;

    // 指令
    public int $command = 0;
    // 发送方的 Channel ID
    public string $channelId = '';
    // 序列号
    public int $sequence = 0;
    // 标识
    protected int $flag = 0;
    // 状态
    protected string $status = '';
    // 目标类型
    protected int $destinationType = 0;
    // 目标对象
    protected array $destination = [];

    protected string $body = '';

    /**
     * 定义协议包格式
     *
     * @return void
     */
    protected function definePacketFormat(): void
    {
        $protocolFormat = (new ProtocolFormat())->addField(ProtocolFormat::DataTypeUint8, 'command')
            ->addField(ProtocolFormat::DataTypeString, 'channelId', false)
            ->addField(ProtocolFormat::DataTypeUint32, 'sequence')
            ->addField(ProtocolFormat::DataTypeUint8, 'flag')
            ->addField(ProtocolFormat::DataTypeString, 'status', false)
            ->addField(ProtocolFormat::DataTypeUint8, 'destinationType')
            ->addField(ProtocolFormat::DataTypeUint32, 'destination', true, 0, true, 0)
            ->addField(ProtocolFormat::DataTypeString, 'body', false);

        $this->packet = new BinaryPacket($protocolFormat);
    }

    /**
     * @return int
     */
    public function getDestinationType(): int
    {
        return $this->destinationType;
    }

    /**
     * @param int $destinationType
     * @return LogicPkt
     */
    public function setDestinationType(int $destinationType): LogicPkt
    {
        $this->destinationType = $destinationType;
        return $this;
    }

    /**
     * @return array
     */
    public function getDestination(): array
    {
        return $this->destination;
    }

    /**
     * @param array $destination
     * @return LogicPkt
     */
    public function setDestination(array $destination): LogicPkt
    {
        $this->destination = $destination;
        return $this;
    }


    /**
     * @return int
     */
    public function getCommand(): int
    {
        return $this->command;
    }

    /**
     * @param int $command
     * @return LogicPkt
     */
    public function setCommand(int $command): LogicPkt
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @param string $channelId
     * @return LogicPkt
     */
    public function setChannelId(string $channelId): LogicPkt
    {
        $this->channelId = $channelId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     * @return LogicPkt
     */
    public function setSequence(int $sequence): LogicPkt
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * @return int
     */
    public function getFlag(): int
    {
        return $this->flag;
    }

    /**
     * @param int $flag
     * @return LogicPkt
     */
    public function setFlag(int $flag): LogicPkt
    {
        $this->flag = $flag;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return LogicPkt
     */
    public function setStatus(string $status): LogicPkt
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return LogicPkt
     */
    public function setBody(string $body): LogicPkt
    {
        $this->body = $body;
        return $this;
    }


}