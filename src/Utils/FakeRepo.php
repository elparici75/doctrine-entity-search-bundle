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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FakeRepo
{
    private $doctrine;
    private $qbSelector = 'e';
    public $entities_mapping;
    private $pager;
    private $handler;
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $doctrine,
        Pager $pager,
        Handler $handler,
        TokenStorageInterface $tokenStorage,
        $entities_mapping
    ) {
        $this->doctrine = $doctrine;
        $this->entities_mapping = $entities_mapping;
        $this->pager = $pager;
        $this->handler = $handler;
        $this->tokenStorage = $tokenStorage;
    }

    public function searchByParams(string $mapping_id, string $terms, array $userParams, int $page, string $_locale): array
    {
        $params = $this->getParamsFromMappingId($mapping_id);
        $repo = $this->doctrine->getRepository($params['class']);
        $qb = $repo->createQueryBuilder($this->qbSelector);
        $selectStr = $this->qbSelector;

        // sanitize and split search terms
        $query = $this->sanitizeSearchQuery($terms);
        $searchTerms = $this->extractSearchTerms($query);

        // prepare eventual innerJoin selectors
        if (\count($params['innerJoin']) > 0) {
            $selectStr = $this->handler->getSelectors($params['innerJoin']);
        }
        $qb->select($selectStr);

        $qb = $this->handler->selectInnerJoin($qb, $params['innerJoin']);

        // Handle strings likables (orWhere LIKE %term%)
        foreach ($searchTerms as $key => $term) {
            $qb = $this->handler->strings($qb, $params['likables'], $key);
            $qb = $this->handler->addLikeParameter($qb, 't_'.$key, $term, ['%', '%']);
            $qb = $this->handler->innerJoin($qb, $params['innerJoin'], $key);
        }

        // add user session constraints from table header (checkbox values & ordering options)
        $qb = $this->handler->userParams($qb, $userParams);

        // add user field match constraint
        $user = $this->tokenStorage->getToken()->getUser();

        if ($params['user_match'] && $user) {
            foreach ($params['user_match'] as $key => $value) {
                $match = $this->handler->isUserMatch($value['granted_all'], $user);

                if ($match) {
                    $userField = \call_user_func_array([$user, 'get'.ucfirst($value['field'])], []);

                    $qb = $this->handler->andWhereEquals($qb, $value['field'], 'u_'.$key);
                    $qb = $this->handler->addParameter($qb, 'u_'.$key, $userField);
                }
            }
        }

        // get Pagination
        $pagination = $this->pager->createPaginator($qb->getQuery(), $page);
        $response = [];

        // render HTML for both results and navigation
        $response['results'] = $this->pager->renderResults($pagination, $mapping_id, $_locale);
        $response['nav'] = $this->pager->renderNav($pagination);

        return $response;
    }

    protected function getParamsFromMappingId($mapping_id): array
    {
        foreach ($this->entities_mapping as $key => $value) {
            if ($mapping_id === $key) {
                return $value;
            }
        }
        throw new LogicException("Corresponding entity mapping \"'. $mapping_id . '\" was not found", 1);
    }

    /**
     * Removes all non-alphanumeric characters except whitespaces.
     */
    private function sanitizeSearchQuery(string $query): string
    {
        return trim(preg_replace('/[[:space:]]+/', ' ', $query));
    }

    /**
     * Splits the search query into terms and removes the ones which are irrelevant.
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(explode(' ', $searchQuery));

        return array_filter($terms, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }
}
