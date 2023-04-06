<?php

namespace Elegance\Datalayer\Scheme;

class SchemeTable
{

    protected $name;
    protected $map;

    /** @var SchemeField[] */
    protected $fields = [];

    protected $isDroped = false;

    function __construct(string $name, array $map = [], ?array $realMap = null)
    {
        $realMap = $realMap ?? SchemeMap::TABLE_MAP;

        $map['comment'] = $map['comment'] ?? $realMap['comment'];
        $map['fields'] = $map['fields'] ?? $realMap['fields'];

        $this->name = $name;
        $this->map = $map;
    }

    /** Marca/Desmarca o campo para a remoção */
    function drop(bool $drop = true): static
    {
        $this->isDroped = boolval($drop);
        return $this;
    }

    #==| Alterações |==#

    /** Define o comentário do campo */
    function comment(string $comment): static
    {
        $this->map['comment'] = $comment;
        return $this;
    }

    /** Defini/Altera varios campos da tabela */
    function fields(): static
    {
        foreach (func_get_args() as $field) {
            if (is_array($field)) {
                $this->fields(...array_values($field));
            } else if (is_class($field, SchemeField::class)) {
                $this->fields[$field->getName()] = $field;
            } else {
                $this->field($field);
            }
        }
        return $this;
    }

    #==| Recuperar de valores |==#

    /** Retorna um objeto de campo da tabela */
    function &field(string $fieldName): SchemeField
    {
        if (!isset($this->fields[$fieldName]))
            $this->fields[$fieldName] = new SchemeField($fieldName, $this->map['fields'][$fieldName] ?? []);

        return $this->fields[$fieldName];
    }

    /** Retorna o mapa de alteração da tabela */
    function getTableAlterMap(): bool|array
    {
        if ($this->isDroped)
            return false;

        $fields = [];
        foreach ($this->fields as $name => $field) {
            $field = $field->getFildMap();
            if ($field || isset($this->map['fields'][$name]))
                $fields[$name] = $field;
        }

        return [
            'comment' => $this->map['comment'],
            'fields' => $fields,
        ];
    }
}
