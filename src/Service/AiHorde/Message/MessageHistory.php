<?php

namespace App\Service\AiHorde\Message;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<int, Message>
 * @implements ArrayAccess<int, Message>
 */
final class MessageHistory implements IteratorAggregate, ArrayAccess, Countable, JsonSerializable
{
    /**
     * @var array<Message>
     */
    private array $messages;

    public function __construct(Message ...$messages)
    {
        $this->messages = $messages;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->messages);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->messages[$offset]);
    }

    public function offsetGet(mixed $offset): Message
    {
        return $this->messages[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Message) {
            throw new InvalidArgumentException('Only instances of ' . Message::class . ' are supported');
        }
        if ($offset !== null) {
            $this->messages[$offset] = $value;
        } else {
            $this->messages[] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->messages[$offset]);
    }

    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * @return array<array{role: string, content: string}>
     */
    public function jsonSerialize(): array
    {
        return array_map(fn (Message $message) => $message->jsonSerialize(), $this->messages);
    }
}
