<?php

namespace ProtocolPacketEncode;

class JsonPacket implements PacketInterface
{
    // 协议格式
    protected ProtocolFormat $format;

    /**
     * 创建一个序列化对象
     *
     * @param ProtocolFormat $format 协议格式定义
     */
    public function __construct(ProtocolFormat $format)
    {
        $this->format = $format;
    }

    /**
     * 编码
     *
     * @param Protocol $protocol 需要编码的协议包对象
     * @return string 序列化后的字符串
     */
    public function encode(Protocol $protocol): string
    {
        $values = [];
        /**
         * @var ProtocolFieldFormat $fieldFormat
         */
        foreach ($this->format->getFormat() as $fieldFormat) {
            $methodName = self::getMethodName($fieldFormat->name);
            if (method_exists($protocol, $methodName) === true) {
                $values[$fieldFormat->name] = $protocol->{$methodName}();
            } else {
                throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
            }
        }

        return json_encode($values);
    }

    /**
     * 解码
     *
     * @param Protocol $protocol 协议包对象 => 在解码后会将数据注入到协议包中
     * @param string &$data 需要解析的协议包
     * @return void
     */
    public function decode(Protocol $protocol, string &$data): void
    {
        $data = json_decode($data, true);

        /**
         * @var ProtocolFieldFormat $fieldFormat
         */
        foreach ($this->format->getFormat() as $fieldFormat) {
            $methodName = self::getMethodName($fieldFormat->name, true);
            if (method_exists($protocol, $methodName) === false) {
                throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
            } else {
                if (isset($data[$fieldFormat->name]) === true) {
                    $value = $data[$fieldFormat->name];
                } else {
                    throw new RuntimeException(sprintf('No %s key', $fieldFormat->name));
                }
                if ($fieldFormat->isInject === true) {
                    $protocol->{$methodName}($value);
                }
            }
        }
    }

    /**
     * 获取字段的 get 或 set 方法名
     *
     * @param string $fieldName 字段名称
     * @param bool $isSet 是否是 set 方法 [default = false]
     * @return string
     */
    protected static function getMethodName(string $fieldName, bool $isSet = false): string
    {
        if ($isSet === false) {
            return 'get' . ucfirst($fieldName);
        }
        return 'set' . ucfirst($fieldName);
    }
}