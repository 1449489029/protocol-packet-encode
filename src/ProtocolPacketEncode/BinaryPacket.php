<?php

namespace ProtocolPacketEncode;

class BinaryPacket implements PacketInterface
{
    // 协议格式
    protected ProtocolFormat $format;

    // 数据类型映射关系 (大端字节序)
    public const DataTypeMap = [
        ProtocolFormat::DataTypeInt8 => 'c',
        ProtocolFormat::DataTypeUint8 => 'C',
        ProtocolFormat::DataTypeInt16 => 's',
        ProtocolFormat::DataTypeUint16 => 'n',
        ProtocolFormat::DataTypeInt32 => 'l',
        ProtocolFormat::DataTypeUint32 => 'N',
        ProtocolFormat::DataTypeInt64 => 'q',
        ProtocolFormat::DataTypeUint64 => 'J',
        ProtocolFormat::DataTypeFloat => 'G',
        ProtocolFormat::DataTypeDouble => 'E',
        ProtocolFormat::DataTypeString => 'a',
    ];

    // 定义各个数据类型的字节长度
    public const DataTypesLength = [
        ProtocolFormat::DataTypeInt8 => 1,
        ProtocolFormat::DataTypeUint8 => 1,
        ProtocolFormat::DataTypeInt16 => 2,
        ProtocolFormat::DataTypeUint16 => 2,
        ProtocolFormat::DataTypeInt32 => 4,
        ProtocolFormat::DataTypeUint32 => 4,
        ProtocolFormat::DataTypeInt64 => 8,
        ProtocolFormat::DataTypeUint64 => 8,
    ];

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
     * @return string 编码后的字符串
     */
    public function encode(Protocol $protocol): string
    {
        $totalValues = [];
        $totalFormats = [];
        /**
         * @var ProtocolFieldFormat $fieldFormat
         */
        foreach ($this->format->getFormat() as $fieldFormat) {
            $methodName = self::getMethodName($fieldFormat->name);
            if (method_exists($protocol, $methodName) === true) {
                $value = $protocol->{$methodName}();
            } else {
                throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
            }

            // 非数组字段
            if ($fieldFormat->isArray === false) {
                [$values, $formats] = $this->encodeNotArrayField($protocol, $fieldFormat);
            } else {
                // 数组字段
                [$values, $formats] = $this->encodeArrayField($protocol, $fieldFormat);
            }

            $totalValues = array_merge($totalValues, $values);
            $totalFormats = array_merge($totalFormats, $formats);
        }

        $totalFormats = implode('', $totalFormats);

        return pack($totalFormats, ...$totalValues);
    }

    /**
     * 解码
     *
     * @param Protocol $protocol 协议包对象 => 在解码后会将数据注入到协议包中
     * @param string $data 需要解析的协议包
     * @return void
     */
    public function decode(Protocol $protocol, string $data): void
    {
        /**
         * @var ProtocolFieldFormat $fieldFormat
         */
        foreach ($this->format->getFormat() as $fieldFormat) {
            // 是否是数组格式
            if ($fieldFormat->isArray === true) {
                if ($fieldFormat->arrayLength > 0) {
                    $length = $fieldFormat->arrayLength;
                } else {
                    $length = $this->readVariableLength($data);
                }
                $values = [];
                for ($i = 0; $i < $length; $i++) {

                    // 如果是可变长度的字段 并且 字段类型为字符串
                    if ($fieldFormat->isFixedLength === false) {
                        // 解析可变长度字段
                        $values[] = $this->analysisVariableLengthField($fieldFormat->type, $data);
                    } else {
                        // 解析固定长度字段
                        $values[] = $this->analysisFixedLengthField($fieldFormat->type, $data, $fieldFormat->arrayLength);
                    }
                }
                // 注入字段值
                $this->injectFieldValue($protocol, $fieldFormat, $values);
            } else {
                // 如果是可变长度的字段 并且 字段类型为字符串
                if ($fieldFormat->isFixedLength === false) {
                    // 解析可变长度字段
                    $value = $this->analysisVariableLengthField($fieldFormat->type, $data);
                } else {
                    // 解析固定长度字段
                    $value = $this->analysisFixedLengthField($fieldFormat->type, $data, $fieldFormat->arrayLength);
                }
                // 注入字段值
                $this->injectFieldValue($protocol, $fieldFormat, $value);
            }


        }
    }

    protected function encodeArrayField(Protocol $protocol, ProtocolFieldFormat $fieldFormat): array
    {
        $methodName = self::getMethodName($fieldFormat->name);
        if (method_exists($protocol, $methodName) === false) {
            throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
        }

        $values = $formats = [];

        // 获取字段值
        $fieldValue = $protocol->{$methodName}();

        // 定长数组
        if ($fieldFormat->arrayLength > 0) {
            // 存数组长度
            $values[] = $fieldFormat->arrayLength;
            $formats[] = self::getType(ProtocolFormat::DataTypeUint32);

            for ($i = 0; $i < $fieldFormat->arrayLength; $i++) {
                if (isset($fieldValue[$i]) === true) {
                    if ($fieldFormat->isFixedLength === false) {
                        [$sonValues, $sonFormats] = $this->encodeNotArrayVariableLengthField($fieldValue[$i], $fieldFormat->type, $fieldFormat->name);
                    } else {
                        [$sonValues, $sonFormats] = $this->encodeNotArrayFixedLengthField($fieldValue[$i], $fieldFormat->type, $fieldFormat->realityLength);
                    }
                } else {
                    // 无效偏移量
                    throw new RuntimeException(sprintf('Invalid offset %d for class %s %s attribute', $i, $protocol::class, $fieldFormat->name));
                }
                $values = array_merge($values, $sonValues);
                $formats = array_merge($formats, $sonFormats);
            }
        } else {
            // 存数组长度
            $values[] =  count($fieldValue);
            $formats[] = self::getType(ProtocolFormat::DataTypeUint32);

            // 非定长数组
            foreach ($fieldValue as $index => $val) {
                if ($fieldFormat->isFixedLength === false) {
                    [$sonValues, $sonFormats] = $this->encodeNotArrayVariableLengthField($val, $fieldFormat->type, $fieldFormat->name);
                } else {
                    [$sonValues, $sonFormats] = $this->encodeNotArrayFixedLengthField($val, $fieldFormat->type, $fieldFormat->realityLength);
                }
                $values = array_merge($values, $sonValues);
                $formats = array_merge($formats, $sonFormats);
            }
        }

        return [$values, $formats];
    }

    /**
     * 编码非数组类型并且字段值长度可变的字段
     */
    protected function encodeNotArrayVariableLengthField(mixed $fieldValue, int $protocolFieldType, string $fieldName): array
    {
        $values = $formats = [];

        // 计算可变字段的实际长度
        if (
            $protocolFieldType === ProtocolFormat::DataTypeString
        ) {
            $length = strlen($fieldValue);
        } else {
            throw new InvalidVariableLengthDataTypeException(sprintf('invalid variable length data type %s %s', $fieldName, $protocolFieldType));
        }


        $values[] = $length;
        $formats[] = $this->getType(ProtocolFormat::DataTypeUint32);

        // 实际的字段值
        $values[] = $fieldValue;
        $formats[] = $this->getType($protocolFieldType) . $length;

        return [$values, $formats];
    }

    /**
     * 编码非数组类型并且字段值长度固定的字段
     */
    protected function encodeNotArrayFixedLengthField(mixed $fieldValue, int $protocolFieldType, int $realityLength): array
    {
        $values = $formats = [];

        // 如果是这类字段类型，在固定字段长度时，需要设置字段的实际长度。
        if (
            $protocolFieldType === ProtocolFormat::DataTypeString
        ) {
            $values[] = $realityLength;
            $formats[] = $this->getType(ProtocolFormat::DataTypeUint32);

            // 实际的值
            $values[] = $fieldValue;
            $formats[] = $this->getType($protocolFieldType) . $realityLength;
        } else {
            // 实际的值
            $values[] = $fieldValue;
            $formats[] = $this->getType($protocolFieldType);
        }

        return [$values, $formats];
    }

    protected function encodeNotArrayField(Protocol $protocol, ProtocolFieldFormat $fieldFormat): array
    {
        $methodName = self::getMethodName($fieldFormat->name);
        if (method_exists($protocol, $methodName) === false) {
            throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
        }

        // 可变长度
        if ($fieldFormat->isFixedLength === false) {

            [$values, $formats] = $this->encodeNotArrayVariableLengthField($protocol->{$methodName}(), $fieldFormat->type, $fieldFormat->name);

            // 固定长度
        } else {
            [$values, $formats] = $this->encodeNotArrayFixedLengthField($protocol->{$methodName}(), $fieldFormat->type, $fieldFormat->realityLength);
        }

        return [$values, $formats];
    }

    /**
     * 解析固定长度字段值
     *
     * @param Protocol $protocol 协议包对象
     * @param ProtocolFieldFormat $fieldFormat 协议字段格式
     * @param string &$data 需要解析的协议包
     * @return mixed
     */
    protected function analysisFixedLengthField(int $fieldFormatType, string &$data, int $realityLength = 0): mixed
    {
        if (
            $fieldFormatType === ProtocolFormat::DataTypeString
        ) {
            $format = $this->getType($fieldFormatType) . $realityLength;
        } else {
            $format = $this->getType($fieldFormatType);
        }
        $value = $this->unpack($format, $data);


        $data = substr($data, $this->getTypeLength($fieldFormatType));

        return $value;
    }

    /**
     * 读取固定长度的数组字段
     *
     * @param Protocol $protocol
     * @param ProtocolFieldFormat $fieldFormat
     * @param string $data
     */
    protected function analysisFixedLengthArrayField(Protocol $protocol, ProtocolFieldFormat $fieldFormat, string &$data)
    {

    }

    /**
     * 解析可变长度字段值
     *
     * @param Protocol $protocol 协议包对象
     * @param ProtocolFieldFormat $fieldFormat 协议字段格式
     * @param string &$data 需要解析的协议包
     * @return mixed
     */
    protected function analysisVariableLengthField(int $fieldFormatType, string &$data): mixed
    {
        $length = $this->readVariableLength($data);

        if ($length > 0) {
            // 解析协议字段值
            $value = $this->unpack($this->getType($fieldFormatType) . $length, $data);

            // 减去已解析的协议包
            $data = substr($data, $length);
        } else {
            $value = null;
        }

        return $value;
    }

    protected function readVariableLength(string &$data): int
    {
        // 获取长度
        $length = $this->unpack($this->getType(ProtocolFormat::DataTypeUint32), $data);
        $data = substr($data, 4);

        return $length;
    }

    /**
     * 注入解析出来的字段值
     *
     * @param Protocol $protocol 协议包对象
     * @param ProtocolFieldFormat $fieldFormat 协议字段格式
     * @param mixed $value 要注入的值
     */
    protected function injectFieldValue(Protocol $protocol, ProtocolFieldFormat $fieldFormat, mixed $value): void
    {
        if ($fieldFormat->isInject === false) return;

        $methodName = self::getMethodName($fieldFormat->name, true);
        if (method_exists($protocol, $methodName) === false) {
            throw new UndefinedMethodException(sprintf('class %s undefined method %s', $protocol::class, $methodName));
        }
        if (!empty($value)) {
            $protocol->{$methodName}($value);
        }
    }

    /**
     * 对单个字段进行解析
     *
     * @param string $format 协议字段格式
     * @param string $data 带解析的协议包
     * @return mixed
     */
    protected function unpack(string $format, string $data): mixed
    {
        $unpackResult = unpack($format, $data);
        if (isset($unpackResult[1]) === true) {
            return $unpackResult[1];
        } else {
            throw new UnpackException(sprintf('format %s unpack failed', $format));
        }
    }

    /**
     * 获取类型的映射
     *
     * @param int $type 类型
     * @return string
     */
    protected function getType(int $type): string
    {
        if (isset(self::DataTypeMap[$type]) === false) throw new InvalidDataTypeException(sprintf('invalid type %s', $type));

        return self::DataTypeMap[$type];
    }

    /**
     * 获取类型的长度
     *
     * @param int $type 类型
     * @return string
     */
    protected function getTypeLength(int $type): string
    {
        if (isset(self::DataTypesLength[$type]) === false) throw new InvalidDataTypeException(sprintf('invalid type %s', $type));

        return self::DataTypesLength[$type];
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