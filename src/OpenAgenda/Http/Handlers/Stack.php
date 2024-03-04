<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Http\Handlers;

class Stack
{
    protected array $handlers = [];

    public static function create(): self
    {
        $stack = new self();
        $stack->push(new UnauthenticatedHandler(), 'unauthenticated');
        $stack->push(new ResourceNotFoundHandler(), 'notfound');
        $stack->push(new BadRequestHandler(), 'badrequest');
        $stack->push(new ServerErrorHandler(), 'servererror');

        return $stack;
    }

    public function get(): array
    {
        return $this->handlers;
    }

    public function push(HandlerInterface $handler, string $name): self
    {
        $this->handlers[$name] = $handler;

        return $this;
    }

    public function pull(string $name): self
    {
        unset($this->handlers[$name]);

        return $this;
    }

    public function before(string $findName, HandlerInterface $handler, string $name): self
    {
        return $this->splice($findName, $name, $handler, true);
    }

    public function after(string $findName, HandlerInterface $handler, string $name): self
    {
        return $this->splice($findName, $name, $handler, false);
    }

    /**
     * Insert a handler before or after the given $findName handler.
     */
    protected function splice(
        string $findName,
        string $handlerName,
        HandlerInterface $handler,
        bool $before
    ): self {
        $keys = array_reverse(array_keys($this->handlers));
        if (! isset($this->handlers[$findName])) {
            return $this->push($handler, $handlerName);
        }

        $offset = $before ? $keys[$findName] + 1 : $keys[$findName] + 2;

        $this->handlers = array_slice($this->handlers, 0, $offset, true) +
            [$handlerName => $handler] +
            array_slice($this->handlers, $offset, null, true);

        return $this;
    }
}
