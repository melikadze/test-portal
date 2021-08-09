@extends('layouts.app')

@section('content')
    
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div><h5>{{ $article->title }}</h5></div>
                    <div>
                        @include('articles.buttons.manage')
                        @include('articles.buttons.back')
                    </div>
                </div>

                <div class="card-body">
                    
                    <img src="{{ $article->image }}" alt="{{ $article->title }}" class="card float-left mr-3" style="width: 25rem;">
                    {{ $article->body }}
                    
                    <blockquote class="blockquote mt-2">
                        <footer class="blockquote-footer">By <cite title="Source Title">{{ $article->user->name }}</cite> <small class="text-muted float-right">{{ $article->created_at->diffForHumans() }}</small></footer>
                    </blockquote>
                </div>
        </div>
    </div>
</div>

@endsection