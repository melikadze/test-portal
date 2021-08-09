<?php

namespace App\Repositories;

use Exception;
use App\Models\User;
use App\Models\Article;
use App\Http\Filters\FilterDatatable;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Request;

class ArticleRepository 
{
    /**
     * @var Article $article
     */
    protected $article;


    /**
     * Initialize article repository
     * 
     * @param Article $article
     * @return VOID
     */
    public function __construct(Article $article)
    {
       $this->article = $article; 
    }


    /**
     * Create new article
     * 
     * @param Array  $attributes
     * @return Article
     */
    public function create( $attributes )
    {
        return $this->article->create($attributes);
    }

    /**
     * Fetch Data for API
     * 
     * @param String $email
     * @return QueryBuilder 
     */
    public function fetchForApi($email)
    {
        $user = User::where('email', $email)->firstOrFail();
    
        return QueryBuilder::for( $this->article )
            ->with(['user'])
            ->allowedFilters( array_merge(
                $this->article->allowedFilters,
                [AllowedFilter::custom('datatable', (new FilterDatatable())->setFields( $this->article->allowedFilters ))],
            ) )
            ->allowedSorts( $this->article->allowedSorts )
            ->where('user_id', $user->id)
            ->paginate()->appends(['sort' => Request::query('sort'), 'search' => Request::query('filter')]);
    }
}