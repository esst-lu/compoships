<?php

namespace Awobaz\Compoships\Database\Query;

use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Support\Facades\DB;

class Builder extends BaseQueryBuilder
{
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        // implement custom support for multi-column 'IN'
        if (is_array($column)) {
            $this->where(function ($query) use ($column, $values) {
                // build left values
                $left = 'CONCAT(';
                foreach ($column as $attribute) {
                    $left .= "CONVERT($attribute, CHAR)";
                    $left .= ", '-', ";
                }
                $left = rtrim($left, ", '-',");
                $left .= ')';

                $left = DB::raw("LEFT($left, LENGTH($left))");

                // build right values side
                $right = [];
                foreach ($values as $value) {
                    $right[] = implode("-", $value);
                }

                $query->whereIn($left, $right);
            });

            /*$this->where(function ($query) use ($column, $values) {
                foreach ($values as $value) {
                    $query->orWhere(function ($query) use ($column, $value) {
                        foreach ($column as $index => $aColumn) {
                            $query->where($aColumn, $value[$index]);
                        }
                    });
                }
            });*/

            return $this;
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        // If the column and values are arrays, we will assume it is a multi-columns relationship
        // and we adjust the 'where' clauses accordingly
        if (is_array($first) && is_array($second)) {
            $type = 'Column';

            foreach ($first as $index => $f) {
                $this->wheres[] = [
                    'type'     => $type,
                    'first'    => $f,
                    'operator' => $operator,
                    'second'   => $second[$index],
                    'boolean'  => $boolean,

                ];
            }

            return $this;
        }

        return parent::whereColumn($first, $operator, $second, $boolean);
    }
}
