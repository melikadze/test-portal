<a href="{{ $article->path() }}" class="list-group-item list-group-item-action flex-column align-items-start">
    <div class="d-flex w-100 justify-content-between">
    <h5 class="mb-1">{{ $article->title }}</h5>
    <small class="text-muted">{{ $article->created_at->diffForHumans() }}</small>
    </div>
    <p class="mb-1">{{ $article->excerpt }}</p>
    <small class="text-muted">By {{ $article->user->name }}</small>
</a>