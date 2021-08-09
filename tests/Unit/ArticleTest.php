<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;
   
    /** @test */
    public function it_has_path()
    {
        $article = Article::factory()->create();

        $this->assertEquals( "/articles/{$article->id}", $article->path() );
    }


    /** @test */
    public function it_belongs_user()
    {
        $article = Article::factory()->create();

        $this->assertInstanceOf(User::class, $article->user);
    }

}
