<?php

namespace Tests\Feature;


use Tests\TestCase;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use RefreshDatabase, WithFaker;
  
    /** @test */
    public function  guests_cannot_manage_article()
    {
        $article = Article::factory()->create();
        

        $this->post('/articles', $article->toArray())->assertRedirect('/login');

        $this->get($article->path())->assertRedirect('/login');

        $this->get('/articles')->assertRedirect('/login');

        $this->get('/articles/create')->assertRedirect('/login');

        $this->get($article->path() . '/edit')->assertRedirect('/login');

        $this->put($article->path())->assertRedirect('/login');
    }

    /** @test */
    public function user_can_create_article() : void
    {
        $this->authorized();

        $this->get('/articles/create')->assertOk();

        Storage::fake('images');
        
        $attributes = [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'image' => UploadedFile::fake()->image('article-thumbnail.jpg')
        ];

        $this->followingRedirects()->post('/articles', $attributes)
             ->assertSee($attributes['title'])
             ->assertSee($attributes['body']);


        unset($attributes['image']);
        
        $this->assertDatabaseHas('articles', $attributes );

        $this->get('/articles')->assertSee($attributes['title']);


        $latestArticle = article::find(1);

        $this->assertFileExists('public' .  $latestArticle->image);

        $base_dir = realpath($_SERVER["DOCUMENT_ROOT"]);
                
        unlink("{$base_dir}/public{$latestArticle->image}");

        $this->assertFileDoesNotExist('public' .  $latestArticle->image);
    }
    
    /** @test */
    public function owner_can_edit_article()
    {
        $article = Article::factory()->create([
            'user_id' => $this->authorized()->id
        ]);

        $this->get( $article->path() . '/edit' )
             ->assertOk()
             ->assertSee($article->title)
             ->assertSee($article->body);
        
        $newAttributes = [
            'title' => 'changed title',
            'body' => 'modified body'
        ];

        $this->followingRedirects()->put( $article->path(), $newAttributes)
             ->assertSee($newAttributes['title'])
             ->assertSee($newAttributes['body']);
    }

    /** @test */
    public function only_owner_can_edit_article()
    {
        $article = Article::factory()->create();
        $this->authorized();

        $this->get( $article->path() . '/edit' )->assertForbidden();
        
        $newAttributes = [
            'title' => 'changed title'
        ];

        $this->followingRedirects()->put( $article->path(), $newAttributes)->assertForbidden();
    }

    /** @test */
    public function owner_can_delete_article()
    {
        $attributes = Article::factory()->raw([
            'user_id' => $this->authorized()->id
        ]);

        $article = Article::create($attributes);

        $this->delete( $article->path() )->assertRedirect('/articles');
        
        $this->assertDatabaseMissing('articles', $attributes);
    }

    /** @test */
    public function only_owner_can_delete_article()
    {
        $attributes = Article::factory()->raw();

        $article = Article::create($attributes);

        $this->authorized();

        $this->delete( $article->path() )->assertForbidden();
        
        $this->assertDatabaseHas('articles', $attributes);
    }
    
    /** @test */
    public function user_can_view_only_own_articles()
    {
        $article = Article::factory()->create([
            'user_id' => $this->authorized()->id
        ]);

        $this->get( $article->path() )
             ->assertSee($article->title)
             ->assertSee($article->body)
             ->assertSee($article->image);

        $anotherArticle = Article::factory()->create();

        $this->get( $anotherArticle->path() )->assertForbidden();
    }

    /** @test */
    public function  article_requires_title()
    {
        $this->authorized();

        $attributes = Article::factory()->raw([ 'title' => '' ]);

        $this->post('/articles', $attributes)->assertSessionHasErrors('title');
    }

    /** @test */
    public function  article_requires_body()
    {
        $this->authorized();

        $attributes = Article::factory()->raw([ 'body' => '' ]);

        $this->post('/articles', $attributes)->assertSessionHasErrors('body');
    }

    /** @test */
    public function  article_requires_image()
    {
        $this->authorized();
        
        $attributes = Article::factory()->raw([ 'image' => '' ]);

        $this->post('/articles', $attributes)->assertSessionHasErrors('image');

        $attributes['image'] = 'test.jpg';

        $this->post('/articles', $attributes)->assertSessionHasErrors('image');
    }

    
}
