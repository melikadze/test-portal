@extends('layouts.app')

@section('content')

    <form action="{{ $article->path() }}" method="POST" enctype="multipart/form-data" class="container">
        @csrf
        @method('PUT')
        <div class="row justify-content-center">
            <div class="col-md-12 ">
                <div class="card ">
                    <div class="card-header d-flex justify-content-between">
                        <div><h5>{{ __('Edit Article') }}</h5></div>
                        <div>@include('articles.buttons.back', [ 'url' => $article->path() ])</div>
                    </div>
        

        <div class="field px-2">
            <label for="title" class="label">Title</label>

            <div class="control">
                <input type="text"  name="title" value="{{ $article->title }}"  class="input form-control ">
            </div>
        </div>

        <div class="field px-2">
            <label for="body" class="label">Body</label>

            <div class="control">
                <textarea name="body"  class="textarea form-control" style="min-height: 200px;">{{ $article->body }}</textarea>
            </div>
        </div>

        <div class="field px-2">
            <label for="image"  class="label">Image</label>

            <div class="control">
                <input type="file"  name="image"  accept="image/*"  class="form-control-file">
            </div>
        </div>

        <br>
        @if (count($errors) > 0)
            @include('articles.errors')
        @endif

        <div class="field  px-2">

            <div class="control float-right mb-2">
                <button type="submit"  placeholder="Title" class="btn btn-primary">Update</button>
            </div>
        </div>

                </div>
            </div>
        </div>
    </form>
    
@endsection