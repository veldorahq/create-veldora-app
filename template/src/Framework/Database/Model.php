<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use Veldora\Framework\Database\Relations\BelongsTo;
use Veldora\Framework\Database\Relations\HasMany;
use Veldora\Framework\Database\Relations\HasOne;
use Veldora\Framework\Database\Relations\Relation;
use Veldora\Framework\Foundation\Application;

/**
 * @phpstan-consistent-constructor
 */
abstract class Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = null;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * The model attributes.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * The model's original attributes.
     *
     * @var array<string, mixed>
     */
    protected array $original = [];

    /**
     * Indicates if the model exists in the database.
     */
    protected bool $exists = false;

    /**
     * Indicates if the model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * Indicates if the model uses soft deletes.
     */
    protected bool $softDelete = false;

    /**
     * Create a new model instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the active database connection.
     */
    protected static function getConnection(): Connection
    {
        return Application::getInstance()->get(Connection::class);
    }

    /**
     * Get a new QueryBuilder instance for the model's table.
     */
    public function query(): QueryBuilder
    {
        return (new QueryBuilder(static::getConnection()))->table($this->getTable());
    }

    /**
     * Get the table name associated with the model.
     */
    public function getTable(): string
    {
        if ($this->table !== null) {
            return $this->table;
        }

        $class = (string) strrchr(static::class, '\\');
        $basename = ltrim($class !== '' ? $class : static::class, '\\');
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $basename));

        if (str_ends_with($snake, 'y')) {
            return substr($snake, 0, -1) . 'ies';
        }

        return $snake . 's';
    }

    /**
     * Get the default foreign key name for the model.
     */
    public function getForeignKey(): string
    {
        $class = (string) strrchr(static::class, '\\');
        $basename = ltrim($class !== '' ? $class : static::class, '\\');
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $basename)) . '_id';
    }

    /**
     * Create a new model instance loaded from database row attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function newFromBuilder(array $attributes): static
    {
        $model = new static();
        $model->attributes = $attributes;
        $model->original = $attributes;
        $model->exists = true;
        return $model;
    }

    /**
     * Get a specific attribute value.
     */
    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relation) {
                $result = $relation->getResults();
                $this->attributes[$key] = $result;
                return $result;
            }
        }

        return null;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamic getter.
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamic setter.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamic isset checker.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Save the model to the database.
     */
    public function save(): bool
    {
        $now = date('Y-m-d H:i:s');

        if ($this->timestamps) {
            if (!$this->exists && !isset($this->attributes['created_at'])) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            $dirty = array_diff_assoc($this->attributes, $this->original);
            if (empty($dirty)) {
                return true;
            }

            $primaryVal = $this->attributes[$this->primaryKey] ?? null;
            if ($primaryVal === null) {
                return false;
            }

            $affected = $this->query()->where($this->primaryKey, '=', $primaryVal)->update($dirty);
            if ($affected > 0) {
                $this->original = $this->attributes;
                return true;
            }
            return false;
        }

        // Insert
        $success = $this->query()->insert($this->attributes);
        if ($success) {
            $this->attributes[$this->primaryKey] = static::getConnection()->getPdo()->lastInsertId();
            $this->original = $this->attributes;
            $this->exists = true;
            return true;
        }

        return false;
    }

    /**
     * Delete the model from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $primaryVal = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryVal === null) {
            return false;
        }

        if ($this->softDelete) {
            $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
            return $this->save();
        }

        $affected = $this->query()->where($this->primaryKey, '=', $primaryVal)->delete();
        if ($affected > 0) {
            $this->exists = false;
            return true;
        }

        return false;
    }

    /**
     * Find a model by its primary key.
     */
    public static function find(mixed $id): ?static
    {
        $model = new static();
        
        $attributes = $model->query()
            ->where($model->primaryKey, '=', $id)
            ->first();

        if ($attributes === null) {
            return null;
        }

        return $model->newFromBuilder($attributes);
    }

    /**
     * Retrieve all records of the model.
     *
     * @return array<static>
     */
    public static function all(): array
    {
        $model = new static();
        $results = $model->query()->get();

        return array_map(fn($row) => $model->newFromBuilder($row), $results);
    }

    /**
     * Define a HasOne relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        /** @var T $instance */
        $instance = new $related();
        
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;

        return new HasOne($instance->query(), $this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a HasMany relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        /** @var T $instance */
        $instance = new $related();

        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;

        return new HasMany($instance->query(), $this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a BelongsTo relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        /** @var T $instance */
        $instance = new $related();

        $foreignKey = $foreignKey ?? $instance->getForeignKey();
        $ownerKey = $ownerKey ?? $instance->primaryKey;

        return new BelongsTo($instance->query(), $this, $instance, $foreignKey, $ownerKey);
    }

    /**
     * Forward static calls to a new QueryBuilder instance.
     *
     * @param array<mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return (new static())->query()->$method(...$parameters);
    }
}
