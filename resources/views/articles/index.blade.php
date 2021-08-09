@extends('layouts.app')

@section('content')
    
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div ><h5>{{ __('Latest Articles') }}</h5></div>
                    <div><a href="/articles/create"><button class="btn btn-primary btn-sm">Create New Article</button></a></div>
                </div>

                <div class="card-body">
                    
                        @forelse ($articles as $article)
                            @include('articles.item')
                        @empty
                            <span>No articles published</span>
                        @endforelse
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection