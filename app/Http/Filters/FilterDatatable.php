<?php

namespace App\Http\Filters;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\FiltersPartial;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;

class FilterDatatable extends FiltersPartial
{
    protected array $fields = [];

    protected array $joinedFields = [];

    protected $callback;

    public function __invoke(Builder $query, $value, string $property)
    {
        if ($property !== 'datatable' || !is_array($value)) {
            parent::__invoke($query, $value, $property);

            return $query;
        }

        $query->where(function (Builder $query) use ($value) {
            $this->build($value, $query);
        });

        return $query;
    }

    /**
     * @param array $fields
     *
     * @return \App\Http\Filters\FilterDatatable
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Allow all fields
     *
     * @return \App\Http\Filters\FilterDatatable
     */
    public function allowAllFields(): self
    {
        $this->fields = ['*'];

        return $this;
    }

    /**
     * Set callback for each filter group
     *
     * @param callable $callback
     *
     * @return \App\Http\Filters\FilterDatatable
     */
    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set joined fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setJoinedFields(array $fields)
    {
        $this->joinedFields = $fields;

        return $this;
    }

    /**
     * Check if all fields are allowed
     *
     * @return bool
     */
    private function allFieldsAllowed(): bool
    {
        return $this->fields == ['*'];
    }

    /**
     * Build a recursive query.
     *
     * @param array $group
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $checkForAllowedFields
     */
    private function build(array $group, Builder $query, bool $checkForAllowedFields = true)
    {
        foreach ($group as $filter) {
            if (empty($filter['field']) && empty($filter['group'])) {
                continue;
            }

            $hasGroup = is_array($filter['group'] ?? false);

            if (($checkForAllowedFields && !$hasGroup) && !$this->allFieldsAllowed() && !in_array($filter['field'], $this->fields)) {
                throw new InvalidFilterQuery(
                    collect($filter['field']), collect($this->fields)
                );
            }

            if ($this->callback) {
                $callback = $this->callback;
                $filter = $callback($filter, $query);

                if (is_null($filter)) {
                    continue;
                }
            }

            if ($hasGroup) {
                $query->where(function (Builder $query) use ($filter) {
                    $this->build($filter['group'], $query);
                });

                continue;
            }

            $field = $filter['field'] ?? null;
            $value = $filter['value'] ?? '';
            $skipTableName = false;

            if ($this->isRelationProperty($query, $field)) {
                $this->withRelationConstraint($query, $filter, $field);
            } else {
                $operator = strtolower($filter['operator'] ?? 'and');
                $where = $operator == 'or' ? 'orWhere' : 'where';

                switch ($filter['condition'] ?? 'CONTAINS') {
                    case 'DOES_NOT_CONTAIN':
                        $condition = 'NOT LIKE';
                        $value = "%$value%";
                        break;
                    case 'EQUAL':
                    case '=':
                        $condition = '=';
                        if (is_array($value)) {
                            $where = $operator == 'or' ? 'orWhereIn' : 'whereIn';
                            $condition = null;
                            $value = array_flatten($value);
                        }
                        break;
                    case 'NOT_EQUAL':
                    case '!=':
                        $condition = '<>';
                        if (is_array($value)) {
                            $where = $operator == 'or' ? 'orWhereNotIn' : 'whereNotIn';
                            $condition = null;
                            $value = array_flatten($value);
                        }
                        break;
                    case 'GREATER_THAN':
                    case '>':
                        $condition = '>';
                        break;
                    case 'LESS_THAN':
                    case '<':
                        $condition = '<';
                        break;
                    case 'GREATER_THAN_OR_EQUAL':
                    case '>=':
                        $condition = '>=';
                        break;
                    case 'LESS_THAN_OR_EQUAL':
                    case '<=':
                        $condition = '<=';
                        break;
                    case 'STARTS_WITH':
                        $condition = 'LIKE';
                        $value = "$value%";
                        break;
                    case 'ENDS_WITH':
                        $condition = 'LIKE';
                        $value = "%$value";
                        break;
                    case 'NULL':
                        $condition = null;
                        $value = null;
                        $where = $operator == 'or' ? 'orWhereNull' : 'whereNull';
                        break;
                    case 'NOT_NULL':
                        $condition = null;
                        $value = null;
                        $where = $operator == 'or' ? 'orWhereNotNull' : 'whereNotNull';
                        break;
                    case 'DOESNT_HAVE':
                        $condition = null;
                        $value = null;
                        $where = $operator == 'or' ? 'orDoesntHave' : 'doesntHave';
                        $skipTableName = true;
                        break;
                    case 'CONTAINS':
                    default:
                        if (is_array($value)) {
                            $value = implode(',', array_filter($value));
                        }

                        $condition = 'LIKE';
                        $value = "%$value%";
                        break;
                }

                if (!$skipTableName && !Str::contains($field, '.') &&
                    !in_array($field, $this->joinedFields) && !array_key_exists($field, $this->joinedFields)) {
                    $field = $query->getModel()->getTable() . '.' . $field;
                } elseif (array_key_exists($field, $this->joinedFields)) {
                    $field = $this->joinedFields[$field] ?? $field;
                }

                if (!is_null($condition)) {
                    $query->$where($field, $condition, $value);
                } elseif (!is_null($value)) {
                    $query->$where($field, $value);
                } else {
                    $query->$where($field);
                }
            }
        }
    }

    protected function withRelationConstraint(Builder $query, $filter, string $property)
    {
        if (!is_array($filter)) {
            return parent::withRelationConstraint($query, $filter, $property);
        }

        [$relation, $property] = collect(explode('.', $filter['field']))
            ->pipe(function (Collection $parts) {
                return [
                    $parts->except(count($parts) - 1)->map([Str::class, 'camel'])->implode('.'),
                    $parts->last(),
                ];
            });

        $where = strtolower($filter['operator'] ?? 'and') == 'or' ? 'orWhereHas' : 'whereHas';

        $query->$where($relation, function (Builder $query) use ($filter, $relation, $property) {
            $this->relationConstraints[] = $filter['field'] = $property;

            $filter['operator'] = 'and';

            $this->build([$filter], $query, false);
        });
    }

    protected function isRelationProperty(Builder $query, string $property): bool
    {
        return parent::isRelationProperty($query, $property) && method_exists($query->getModel(), explode('.', $property)[0]);
    }
}