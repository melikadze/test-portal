<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;

class ArticleService
{
    /**
     * @var ArticleRepository $articleRepository
     */
    protected $articleRepository;

    /**
     * Initialize article repository
     * 
     * @return VOID
     */
    function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }


    /**
     * Create new article
     * 
     * @param StoreArticleRequest $request
     * @return \App\Models\Article
     */
    public function create(StoreArticleRequest $request)
    {
        $attributes = $request->validated();

        if ($request->hasFile('image')) {

            if ($request->file('image')->isValid()) {

                $fileName =  time() . $request->image->getClientOriginalName();
                
                $request->image->storeAs('/public',  $fileName);

                $url = Storage::url($fileName);
            }
        }

        $attributes['image'] = $url;

        return auth()->user()->articles()->create($attributes);
    }


    /**
     * Update the specified article
     *
     * @param  \App\Http\Requests\UpdateArticleRequest  $request
     * @param  Article  $article
     * @return Article
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $attributes = $request->validated();

        if ($request->hasFile('image')) {

            if ($request->file('image')->isValid()) {

                $fileName =  time() . $request->image->getClientOriginalName();
                
                $request->image->storeAs('/public',  $fileName);

                $url = Storage::url($fileName);

                $attributes['image'] = $url;

                $base_dir = realpath($_SERVER["DOCUMENT_ROOT"]);

                unlink("{$base_dir}{$article->image}");
            }
        }

        $article->update($attributes);

        return $article;
    }


    /**
     * Fetch articles for api
     * 
     * @param String $email
     * @return QueryBuilder 
     */
    public function fetchForApi($email)
    {
        return $this->articleRepository->fetchForApi($email);
    }
}