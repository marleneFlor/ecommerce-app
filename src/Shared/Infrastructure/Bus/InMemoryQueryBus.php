<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Query\Query;
use App\Shared\Application\Query\QueryBus;
use App\Shared\Application\Query\QueryHandler;

final class InMemoryQueryBus implements QueryBus
{
    /** @var array<string, QueryHandler> */
    private array $handlers = [];

    /** @param iterable<QueryHandler> $handlers */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $queryClass = $this->resolveQueryClass($handler);
            $this->handlers[$queryClass] = $handler;
        }
    }

    public function ask(Query $query): mixed
    {
        $queryClass = get_class($query);

        if (!isset($this->handlers[$queryClass])) {
            throw new \RuntimeException("No handler found for query: {$queryClass}");
        }

        return ($this->handlers[$queryClass])($query);
    }

    private function resolveQueryClass(QueryHandler $handler): string
    {
        $reflection = new \ReflectionClass($handler);
        $invokeMethod = $reflection->getMethod('__invoke');
        $parameters = $invokeMethod->getParameters();

        return $parameters[0]->getType()->getName();
    }
}
