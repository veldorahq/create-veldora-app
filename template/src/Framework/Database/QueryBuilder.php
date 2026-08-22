<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use PDO;
use RuntimeException;

class QueryBuilder
{
    /**
     * The table name.
     */
    protected string $table = '';

    /**
     * The columns to select.
     *
     * @var array<string>
     */
    protected array $columns = ['*'];

    /**
     * The where constraints.
     *
     * @var array<array{type: string, column: string, operator: string, value: mixed}>
     */
    protected array $wheres = [];

    /**
     * The order by clauses.
     *
     * @var array<array{column: string, direction: string}>
     */
    protected array $orders = [];

    /**
     * The maximum number of records to return.
     */
    protected ?int $limit = null;

    /**
     * The offset to start returning records from.
     */
    protected ?int $offset = null;

    /**
     * The query bindings.
     *
     * @var array<mixed>
     */
    protected array $bindings = [];

    /**
     * Create a new QueryBuilder instance.
     */
    public function __construct(protected Connection $connection)
    {
    }

    /**
     * Set the table target.
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the columns to select.
     *
     * @param array<string>|string $columns
     */
    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * Add a WHERE constraint.
     */
    public function where(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an OR WHERE constraint.
     */
    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an ORDER BY clause.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Set the limit constraint.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the offset constraint.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Compile the SELECT query to SQL.
     */
    public function toSql(): string
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->sanitizeName($this->table);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Compile the where constraints into SQL.
     */
    protected function compileWheres(): string
    {
        $sql = '';

        foreach ($this->wheres as $index => $where) {
            $prefix = $index === 0 ? '' : ' ' . $where['type'] . ' ';
            $column = $this->sanitizeName($where['column']);
            $sql .= $prefix . "{$column} {$where['operator']} ?";
        }

        return $sql;
    }

    /**
     * Compile the orders into SQL.
     */
    protected function compileOrders(): string
    {
        $parts = [];
        foreach ($this->orders as $order) {
            $parts[] = $this->sanitizeName($order['column']) . ' ' . $order['direction'];
        }

        return implode(', ', $parts);
    }

    /**
     * Sanitize table/column identifiers to prevent injection.
     */
    protected function sanitizeName(string $name): string
    {
        // Allow dot notation for table.column
        $parts = explode('.', $name);
        $escaped = [];
        foreach ($parts as $part) {
            $escaped[] = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $part) . '`';
        }
        return implode('.', $escaped);
    }

    /**
     * Execute the SELECT query and return results.
     *
     * @return array<array<string, mixed>>
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $pdo = $this->connection->getPdo();

        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->bindings);

        /** @var array<array<string, mixed>> $results */
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }

    /**
     * Execute the query and return the first result.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();

        return $results[0] ?? null;
    }

    /**
     * Execute an INSERT query.
     *
     * @param array<string, mixed> $values
     */
    public function insert(array $values): bool
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $columns = array_keys($values);
        $escapedColumns = array_map([$this, 'sanitizeName'], $columns);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = 'INSERT INTO ' . $this->sanitizeName($this->table) . 
            ' (' . implode(', ', $escapedColumns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(array_values($values));
    }

    /**
     * Execute an UPDATE query.
     *
     * @param array<string, mixed> $values
     */
    public function update(array $values): int
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sets = [];
        foreach ($values as $column => $value) {
            $sets[] = $this->sanitizeName($column) . ' = ?';
        }

        $sql = 'UPDATE ' . $this->sanitizeName($this->table) . ' SET ' . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);

        $bindings = array_merge(array_values($values), $this->bindings);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    /**
     * Execute a DELETE query.
     */
    public function delete(): int
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sql = 'DELETE FROM ' . $this->sanitizeName($this->table);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * Get the query bindings.
     *
     * @return array<mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
