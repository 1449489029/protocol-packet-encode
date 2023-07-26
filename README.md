# 协议包解析

封装对各种编码类型协议包的处理。
程序员只需要继承 `ProtocolPacketEncode\Protocol`，继承后只需要做两件事：
 - 实现 `definePacketFormat` 方法，该方法主要是定义协议包的数据格式；

例如：
```php
 class BasicPkt extends Protocol
{
    // 省略其他代码 ...
    
    
    /**
     * 定义协议包格式
     * 这里是配置协议包格式的核心
     *
     * @return void
     */
    protected function definePacketFormat(): void
    {
        $protocolFormat = (new ProtocolFormat())
            ->addField(ProtocolFormat::DataTypeUint16, 'code')
            ->addField(ProtocolFormat::DataTypeString, 'body', false);
        // 二进制编码格式
        $this->packet = new BinaryPacket($protocolFormat);
        // JSON 编码格式
        // $this->packet = new JsonPacket($protocolFormat);
    }
}
```
- 其次是定义协议包的字段以及字段的`get`与`set`方法。

例如：

```php
class BasicPkt extends Protocol
{
    // 代码
    public int $code;
    // 消息载体
    public string $body = '';
    
    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    // 省略其他代码 ...
}
```

## 安装

```linux
$ composer require 1449489029/protocol-packet-encode
```

## 支持编码类型：
- 二进制
- JSON

## 示例

### 协议包格式

```
0                   1                   2                   3
0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
|              Code             |          Body Length        |
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
|                             Body                            |
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
|                             Body                            |
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
|                             ....                            |
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
                       Example Basic Packet
```


### 协议包类封装

```php
namespace test;

use ProtocolPacketEncode\Protocol;
use ProtocolPacketEncode\ProtocolFormat;
use ProtocolPacketEncode\BinaryPacket;
use ProtocolPacketEncode\JsonPacket;

class BasicPkt extends Protocol
{
    // 代码类型
    public const PING = 1;
    public const PONG = 2;

    // 代码
    public int $code;
    // 消息载体
    public string $body = '';

    /**
     * 定义协议包格式
     * 这里是配置协议包格式的核心
     *
     * @return void
     */
    protected function definePacketFormat(): void
    {
        $protocolFormat = (new ProtocolFormat())
            ->addField(ProtocolFormat::DataTypeUint16, 'code')
            ->addField(ProtocolFormat::DataTypeString, 'body', false);
        $this->packet = new BinaryPacket($protocolFormat);
//        $this->packet = new JsonPacket($protocolFormat);
    }


    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
}
```

### 单元测试

```php
namespace test;

use PHPUnit\Framework\TestCase;

class BasicPktTest extends TestCase
{

    // 测试Ping消息的打包与解包 带有内容
    public function testPackAndUnpackPingMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PING; // 2 字节
        // 隐藏字段(Body Length) 2 字节
        $body = 'sdasdasdasdas'; // 13 字节
        $bytesLength = 2 + 2 + 13;

        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();
        
        // 验证编码后的长度
        $this->assertEquals(strlen($string), $bytesLength);

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);
        
        // 验证解包后的长度
        $this->assertEquals(0, strlen($string));

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }

    // 测试Pong消息的打包与解包 带有内容
    public function testPackAndUnpackPongMessageWhitBody()
    {
        // 数据准备
        $code = BasicPkt::PONG; // 2 字节
        // 隐藏字段(Body Length)    2 字节
        $body = 'dasdasdasdas'; // 12 字节
        $bytesLength = 2 + 2 + 12;

        // 打包
        $basicPktProtocol = new BasicPkt();
        $basicPktProtocol->setCode($code);
        $basicPktProtocol->setBody($body);
        $string = $basicPktProtocol->encode();
        
        // 验证编码后的长度
        $this->assertEquals(strlen($string), $bytesLength);

        // 解包
        $newBasicPktProtocol = new BasicPkt();
        $newBasicPktProtocol->decode($string);
        
        // 验证解包后的长度
        $this->assertEquals(0, strlen($string));

        // 验证
        $this->assertEquals($newBasicPktProtocol->getCode(), $code);
        $this->assertEquals($newBasicPktProtocol->getBody(), $body);
    }
}
```

运行测试：
```linux
$ composer test
```

