@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection

@section('content')
    <div class="item__image">
        <img class="item__image-content" src="{{ Storage::url('images/' . $item->image_path) }}" alt="{{ $item->name }}">
    </div>

    <div class="item__detail">
        <h2 class="item__name">{{ $item->name }}</h2>
        <div class="brand__name">ブランド名</div>
        <div class="price">￥{{ $item->price }}(値段)</div>

        <div class="favorite">
            @auth
            <form class="favorite__content" action="{{ route('favorite', ['id' => $item->id]) }}" method="post">
            @csrf
                <button class="favorite__button" type="submit">
                    @if($item->isFavoritedBy(Auth::user()))
                        <i class="fa-solid fa-star" style="color: #ec0426;"></i>
                    @else
                        <i class="fa-solid fa-star" style="color: #a7a0a1;"></i>
                    @endif
                </button>
            </form>
            @else
            <a href="{{ route('show.login') }}" class="favorite__button">
                <i class="fa-solid fa-star" style="color: #a7a0a1;"></i>
            </a>
            @endauth
        </div>

        <div class="comment">
            @auth
            <form class="comment__content" action="" method="get">
            @csrf
                <button class="comment__button" type="submit">
                    <i class="fa-regular fa-comment"></i>
                </button>
            </form>
            @else
            <a href="{{ route('show.login') }}" class="favorite__button">
                <i class="fa-regular fa-comment"></i>
            </a>
            @endauth
        </div>
    </div>
@endsection