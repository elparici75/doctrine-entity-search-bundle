<?php

/*
 * This file is part of the Safepass website.
 *
 * (c) HC Conseil <contact@hcconseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elparici\EntitySearchBundle\Controller;

use Elparici\EntitySearchBundle\Utils\FakeRepo;
use Elparici\EntitySearchBundle\Utils\Pager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage search in back.
 *
 * @Route("{_locale}/elparici-entity-search", name="entity_search_")
 */
class EntitySearchController extends AbstractController
{
    private $fakeRepo;
    private $pager;

    public function __construct(FakeRepo $fakeRepo, Pager $pager)
    {
        $this->fakeRepo = $fakeRepo;
        $this->pager = $pager;
    }

    /**
     * searches through specified entity and parameters.
     *
     * @Route(
     *     "/by_params/{data_mapping}/{terms}",
     *     name="by_params",
     *     methods={"GET"},
     *     defaults={ "_locale": "fr", "terms": "" },
     * options={"expose": true})
     */
    public function byParamsAction(Request $request, string $data_mapping = '', string $terms = ''): JsonResponse
    {
        
        return new JsonResponse(
            $this->fakeRepo->searchByParams(
                $request->get('data_mapping'),
                $request->get('terms'),
                $request->get('getQbParams'),
                $request->get('page'),
                $request->get('_locale')
            )
        );
    }

    /**
     * gets basic table gheaders for fix render view.
     *
     * @Route(
     *     "/header/{data_mapping}",
     *     name="get_headers",
     *     methods={"GET"},
     *     defaults={ "_locale": "fr" },
     * options={"expose": true})
     */
    public function headerAction(Request $request, string $data_mapping = ''): JsonResponse
    {
        return new JsonResponse(
            $this->pager->renderHeader(
                $request->get('data_mapping'),
                $request->get('_locale')
            )
        );
    }
}
