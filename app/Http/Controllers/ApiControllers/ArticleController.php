<?php

namespace App\Http\Controllers\ApiControllers;

use App\Models\Article;
use App\Services\ArticleService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleDetailResource;
use App\Http\Resources\ArticleResource;

class ArticleController extends Controller
{
    /**
     * @var ArticleService $articleService
     */
    protected $articleService;

    /**
     * Initialize api article controller
     * 
     * @param ArticleService $articleService
     * @return VOID
     */
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/users/{email}/articles",
     *      operationId="fetchAllArticlesUnderUser",
     *      tags={"Articles"},
     *      summary="Fetch all articles under user",
     *      description="Return All articles",
     *      @OA\Parameter(
     *          name="email",
     *          description="User email for identification",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          description="sort by multiple properties by separating them with a comma",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              format="textarea"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="filter",
     *          description="You can specify multiple matching filter values by passing a comma separated list of values:",
     *          in="query",
     *          @OA\Schema(
     *              type="object",
     *          ),
     *          style="deepObject",
     *          explode="true",
     *          required=false
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Resource Not Found"),)
     *       )
     * )
     */
    public function index($email)
    {
        return ArticleResource::collection($this->articleService->fetchForApi($email));
    }

    /**
     * @OA\Get(
     *      path="/api/v1/articles/{article}",
     *      operationId="fetchSingleArticleDetails",
     *      tags={"Articles"},
     *      summary="Fetch artcile details",
     *      description="Return article details",
     *      @OA\Parameter(
     *          name="article",
     *          description="article identification",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Resource Not Found"),)
     *       )
     * )
     */
    public function show(Article $article)
    {
        return new ArticleDetailResource( $article );
    }
}