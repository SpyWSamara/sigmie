<?php

declare(strict_types=1);

namespace Sigmie\Parse;

use Sigmie\Query\Contracts\QueryClause;
use Sigmie\Query\Queries\Compound\Boolean;
use Sigmie\Query\Queries\MatchNone;
use Sigmie\Query\Queries\Term\IDs;
use Sigmie\Query\Queries\Term\Range;
use Sigmie\Query\Queries\Term\Term;
use Sigmie\Query\Queries\Term\Terms;

class FilterParser extends Parser
{
    protected function parseString(string $query)
    {
        // Replace breaks with spaces
        $query = str_replace(["\r", "\n"], ' ', $query);
        // Remove extra spaces that aren't in quotes
        // and replace them with only one. This regex handles
        // also quotes that are escapted
        $query = preg_replace('/\s+(?=(?:[^\'"]*[\'"][^\'"]*[\'"])*[^\'"]*$)/', ' ', $query);
        // Trim leading and trailing spaces
        $query = trim($query);

        // If first filter is a parenthetic expression
        if (preg_match_all("/^\((((?>[^()]+)|(?R))*)\)/", $query, $matches)) {
            $matchWithParentheses = $matches[0][0];

            //Remove outer parenthesis
            $matchWithoutParentheses = preg_replace('/^\((.+)\)$/', '$1', $matchWithParentheses);

            //Remove the parenthetic expresion from the query
            $query = preg_replace("/((\b(?:AND NOT|AND|OR)\b(?=(?:(?:[^'\"]*['\"]){2})*[^'\"]*$)) )?\({$matchWithoutParentheses}\)/", '', $query);

            //Trim leading and trailing spaces
            $query = trim($query);

            //Create filter from parentheses match
            $filter = $this->parseString($matchWithoutParentheses);
        } else {
            // Split on the first AND NOT, AND or OR operator that is not in quotes
            [$filter] = preg_split('/\b(?:AND NOT|AND|OR)\b(?=(?:(?:[^\'"]*[\'"]){2})*[^\'"]*$)/', $query, limit: 2);
        }

        // A nested filter like (inStock = 1 AND active = true) is
        // returned as an array from the `parseString` method.
        //If it's a string filter like inStock = 1 and not
        //a subquery like (inStock = 1 AND active = true).
        if (is_string($filter)) {
            $query = str_replace($filter, '', $query);
            $query = trim($query);
            $filter = trim($filter);
        }

        $res = ['filter' => $filter];

        if (preg_match('/^(?P<operator>AND NOT|AND|OR)/', $query, $matchWithoutParentheses)) {
            $operator = $matchWithoutParentheses['operator'];
            //Remove operator from the query string
            $query = preg_replace("/^{$operator}/", '', $query);
            $query = trim($query);

            $res['operator'] = $operator;
            $res['values'] = $this->parseString($query);
        }

        return $res;
    }

    public function parse(string $filterString): Boolean
    {
        if (trim($filterString) === '') {
            $bool = new Boolean;
            $bool->must->matchAll();

            return $bool;
        }

        $filters = $this->parseString($filterString);

        $bool = $this->apply($filters);

        return $bool;
    }

    protected function apply(string|array $filters, string $operator = 'AND'): Boolean
    {
        $boolean = new Boolean;

        $filter = is_string($filters) ? $filters : $filters['filter'];
        $operator = $filters['operator'] ?? $operator;
        $values = $filters['values'] ?? null;

        if (is_string($filter) && str_starts_with($filter, 'NOT')) {
            $operator = 'NOT';
            $filter = trim($filter, 'NOT');
            $filter = trim($filter);
            $query1 = $this->stringToQueryClause($filter);
        } elseif (is_string($filter)) {
            $query1 = $this->stringToQueryClause($filter);
        } else {
            $query1 = $this->apply($filter, $operator);
        }

        match ($operator) {
            'AND' => $boolean->must()->query($query1),
            'NOT' => $boolean->mustNot()->query($query1),
            //Using must here is correct, trust me!
            //The `NOT` is handled in the second query
            'AND NOT' => $boolean->must()->query($query1),
            'OR' => $boolean->should()->query($query1),
            default => throw new ParseException("Unmatched filter operator '{$operator}'")
        };

        if ($filters['operator'] ?? false) {
            $query2 = $this->apply($values, $operator);

            match ($operator) {
                'AND' => $boolean->must()->query($query2),
                'AND NOT' => $boolean->mustNot()->query($query2),
                'OR' => $boolean->should()->query($query2),
                default => throw new ParseException("Unmatched filter operator '{$operator}'")
            };
        }

        return $boolean;
    }

    protected function stringToQueryClause(string $string): QueryClause
    {
        $query = match (1) {
            preg_match('/^is:[a-z_A-Z0-9]+/', $string) => $this->handleIs($string),
            preg_match('/^is_not:[a-z_A-Z0-9]+/', $string) => $this->handleIsNot($string),
            preg_match('/^(\w+)([<>]=?)(.+)/', $string) => $this->handleRange($string),
            preg_match('/^(\w+)([<>]=?)(\'.+\')/', $string) => $this->handleRange($string),
            preg_match('/^(\w+)([<>]=?)(\".+\")/', $string) => $this->handleRange($string),
            preg_match('/^_id:[a-z_A-Z0-9]+/', $string) => $this->handleIDs($string),
            preg_match('/\w+:\[.+\]/', $string) => $this->handleIn($string),
            preg_match('/\w+:".+"/', $string) => $this->handleTerm($string),
            preg_match('/\w+:\'.+\'/', $string) => $this->handleTerm($string),
            default => null
        };

        if ($query instanceof QueryClause) {
            return $query;
        }

        $this->handleError("Filter string '{$string}' couldn't be parsed.");

        return new MatchNone;
    }

    public function handleRange(string $range)
    {
        preg_match('/^(\w+)([<>]=?)(("|\')?.+("|\')?)/', $range, $matches);

        $field = $matches[1];
        $operator = $matches[2];
        $value = trim($matches[3], '"\'');

        $field = $this->handleFieldName($field);
        if (is_null($field)) {
            return;
        }

        return new Range($field, [$operator => $value]);
    }

    public function handleIDs(string $id)
    {
        [, $value] = explode(':', $id);

        return new IDs([$value]);
    }

    public function handleIs(string $is)
    {
        [, $field] = explode(':', $is);

        $field = $this->handleFieldName($field);

        if (is_null($field)) {
            return;
        }

        return new Term($field, true);
    }

    public function handleIsNot(string $is)
    {
        [, $field] = explode(':', $is);

        $field = $this->handleFieldName($field);
        if (is_null($field)) {
            return;
        }

        return new Term($field, false);
    }

    public function handleIn(string $terms)
    {
        [$field, $value] = explode(':', $terms);
        $value = trim($value, '[]');
        $values = explode(',', $value);
        // Remove doublue quotes from values
        $values = array_map(fn ($value) => trim($value, '\''), $values);
        // Remove single quotes from values
        $values = array_map(fn ($value) => trim($value, '"'), $values);

        $field = $this->handleFieldName($field);

        if (is_null($field)) {
            return;
        }

        return new Terms($field, $values);
    }

    public function handleTerm(string $term)
    {
        [$field, $value] = explode(':', $term);

        // Remove quotes from value
        $value = trim($value, '\'');
        // Remove quotes from value
        $value = trim($value, '"');

        $field = $this->handleFieldName($field);
        if (is_null($field)) {
            return;
        }

        return new Term($field, $value);
    }
}
