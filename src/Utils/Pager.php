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

use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig\Environment;

class Pager
{
    private $twig;
    private $entities_mapping;
    private $pager_option;

    public function __construct(Environment $twig, $entities_mapping, $pager_option)
    {
        $this->twig = $twig;
        $this->entities_mapping = $entities_mapping;
        $this->pager_option = $pager_option;
    }

    public function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = null;

        switch ($this->pager_option) {
            case 'knp_paginator':
               throw new Exception('This pagination plugin has not been implemented yet... ', 1);
                break;

            case 'pager_fanta':
                $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
                $paginator->setMaxPerPage(25); // @todo inject values from mapping configuration
                $paginator->setCurrentPage($page);
                break;

            default:
               throw new Exception('You should define a pager option in config/packages/entity_search.yaml for pager value ', 1);
                break;
        }

        return $paginator;
    }

    public function renderResults($pagination, string $mapping_id, string $_locale): string
    {
        $html = $this->twig->render(
            '@entity_search_bundle/list.html.twig', [
                'pagination' => $pagination,
                'mapping' => $this->entities_mapping[$mapping_id],
                'pager' => $this->pager_option,
                '_locale' => $_locale
            ]
        );

        return $html;
    }

    public function renderHeader(string $mapping_id, string $_locale)
    {
        $html = $this->twig->render(
            '@entity_search_bundle/card-header.html.twig', [
                'mapping' => $this->entities_mapping[$mapping_id],
                 '_locale' => $_locale
            ]
        );

        return $html;
    }

    public function renderNav($pagination)
    {
        $html = $this->twig->render(
            '@entity_search_bundle/nav.html.twig', [
                'pagination' => $pagination,
                'pager' => $this->pager_option,
            ]
        );

        return $html;
    }
}
