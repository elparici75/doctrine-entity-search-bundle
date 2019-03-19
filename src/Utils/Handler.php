<?php

/*
 * This file is part of the Safepass website.
 *
 * (c) HC Conseil <contact@hcconseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elparici\EntitySearchBundle\Utils;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class Handler
{
    private $qbSelector = 'e';
    private $alphas;
    private $nbArguments = 0;
    private $equalSep = '=';
    private $likeSep = 'LIKE';

    public function __construct($alphas)
    {
        $this->alphas = range($this->qbSelector, 'z');
    }

    public function andWhereEquals(QueryBuilder $qb, string $field, string $key): QueryBuilder
    {
        return $qb->andWhere($this->qbSelector.'.'.$field.' = :'.$key);
    }

    public function isUserMatch(array $grantedRoles, $user)
    {
        $is = true;
        foreach ($grantedRoles as $role) {
            if ($user->hasRole($role)) {
                $is = false;
                break;
            }
        }

        return $is;
    }

    public function userParams(QueryBuilder $qb, array $userParams): QueryBuilder
    {
        // Handle checkbox options
        if (array_key_exists('choices', $userParams)) {
            foreach ($userParams['choices'] as $column => $value) {
                $qb->andWhere($qb->expr()->in($this->qbSelector.'.'.$column, $value));
            }
        }
        // Handle Reordering user options
        if (array_key_exists('orderBy', $userParams)) {
            $qb = $this->orderQuery($qb, $userParams['orderBy']);
        }

        return $qb;
    }

    protected function orderQuery(QueryBuilder $qb, array $params): QueryBuilder
    {
        foreach ($params as $colum => $value) {
            $qb->addOrderBy($this->qbSelector.'.'.$colum, $value);
        }

        return $qb;
    }

    public function strings(QueryBuilder $qb, array $params, string $key): QueryBuilder
    {
        foreach ($params as $value) {
            $this->addStringQbArgument($qb, $value, $key);
            ++$this->nbArguments;
        }

        return $qb;
    }

    public function innerJoin(QueryBuilder $qb, array $children, $key): QueryBuilder
    {
        $selector_count = 1;
        foreach ($children as $child => $field) {
            $qb = $this->addStringInnerQbArgument($qb, $this->alphas[$selector_count], $field, $key);
            ++$selector_count;
        }

        return $qb;
    }

    public function addStringQbArgument(QueryBuilder $qb, string $field, $key): QueryBuilder
    {
        $column = $this->qbSelector.'.'.$field;
        $condition = $column.' '.$this->likeSep.' :t_'.$key;
        if (0 === $this->nbArguments) {
            return $qb->where($condition);
        }

        return $qb->orWhere($condition);
    }

    public function addBoolQbArgument(QueryBuilder $qb, string $field): QueryBuilder
    {
        $column = $this->qbSelector.'.'.$field;
        $condition = $column.' '.$this->equalSep.' :'.$field;

        if (0 === $this->nbArguments) {
            return $qb->where($condition);
        }

        return $qb->andWhere($condition);
    }

    /*
     * @param $likable: expecting an array as ['%', ''], ['', '%'], ['%', '%'] or default value.
     * @param name : as an entity cannot have duplicate name variable, we base name of the var on property name
     */
    public function addLikeParameter(QueryBuilder $qb, string $name, $value, array $likable = ['', '']): QueryBuilder
    {
        return $qb->setParameter($name, $likable[0].$value.$likable[1]);
    }

    public function addParameter(QueryBuilder $qb, string $name, $value): QueryBuilder
    {
        return $qb->setParameter($name, $value);
    }

    public function getSelectors(array $params_innerJoin): string
    {
        $selectors = [$this->qbSelector];

        $selector_count = 1; // preserve the 0 key as it is occupied by the main qbSelector
        foreach ($params_innerJoin as $child) {
            $selectors[] = $this->alphas[$selector_count];
            ++$selector_count;
        }

        return implode(', ', $selectors);
    }

    public function addStringInnerQbArgument(QueryBuilder $qb, string $selector, string $field, $term): QueryBuilder
    {
        $column = $selector.'.'.$field;
        $condition = $column.' '.$this->likeSep.' :t_'.$term;

        return $qb->orWhere($condition);
    }

    // @param string $a  The relationship to join.
    // @param string $b  The alias of the join.   .
    // @param string $c  The condition for the join
    public function selectInnerJoin(QueryBuilder $qb, array $children): QueryBuilder
    {
        $selector_count = 1;

        foreach ($children as $child => $field) {
            $a = $this->qbSelector.'.'.$child;
            $b = $this->alphas[$selector_count];
            $c = $this->alphas[$selector_count].'.id '.$this->equalSep.' '.$this->qbSelector.'.'.$child;

            $qb->innerJoin($a, $b, Join::WITH, $c);

            ++$selector_count;
        }

        return $qb;
    }
}
