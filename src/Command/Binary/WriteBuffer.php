<?php

namespace Zikarsky\React\Gearman\Command\Binary;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Convert CommandInterfaces to a byte-stream
 *
 * @author Benjamin Zikarsky <benjamin@zikarsky.de>
 */
class WriteBuffer
{

    /**
     * @var string
     */
    protected $buffer = "";

    /**
     * Pushes a command to the buffer after converting it to the byte-representation
     */
    public function push(CommandInterface $command)
    {
        $argv = array_values($command->getAll(null, true));
        $body = implode(CommandInterface::ARGUMENT_DELIMITER, $argv);

        $this->buffer .= pack(
            CommandInterface::HEADER_WRITE_FORMAT,
            $command->getMagic(),
            $command->getType(),
            strlen($body)
        );

        $this->buffer .= $body;

        return strlen($this->buffer);
    }

    /**
     * Shifts a number of bytes (default: all) from the beginning of the buffer
     * and returns them
     *
     * @param  int|null                 $numBytes
     * @return string
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public function shift($numBytes = null)
    {
        if ($numBytes !== null && $numBytes < 1) {
            throw new InvalidArgumentException("Can only shift 1 or more bytes");
        }

        $bufLen = strlen($this->buffer);
        $numBytes = $numBytes === null ? $bufLen : intval($numBytes);

        if ($numBytes > $bufLen) {
            throw new OutOfBoundsException("Requested byte coutn exceeds buffer length");
        }

        if ($bufLen == $numBytes) {
            $result = $this->buffer;
            $this->buffer = "";
        } else {
            $result = substr($this->buffer, 0, $numBytes);
            $this->buffer = substr($this->buffer, $numBytes);
        }

        return $result;
    }
}
