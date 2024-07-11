<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\SoldItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
        $userId = auth()->id();

        // 全ての商品を取得
        $allItems = Item::all();

        // ユーザーが購入したアイテムのカテゴリを取得
        $purchasedCategories = SoldItem::where('user_id', $userId)->with('item')->get()->pluck('item.category')->unique();

        // おすすめアイテムをカテゴリから取得
        $recommendedItems = Item::whereIn('category', $purchasedCategories)->get();

        // マイリストに登録されたアイテムを取得
        $favoriteItems = Favorite::where('user_id', $userId)->with('item')->get()->pluck('item');

        } else {
            // 全商品を取得
            $allItems = Item::all();
            $recommendedItems = Item::all();
            $favoriteItems = collect(); // 空のコレクションを作成
        }

        return view('index', compact('allItems', 'recommendedItems', 'favoriteItems'));
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        return view('detail', compact('item'));
    }

    public function showDisplayItem()
    {
        return view ('display');
    }

    public function store(Request $request) {
        $user = Auth::user();

        $imageName = $user->id . '_image' . time() . '.' . $request->file('image')->extension();
        $request->file('image')->storeAs('public/images', $imageName);

        $form = $request->only('category', 'condition', 'name', 'brand', 'description', 'price');
        $form['image_path'] = $imageName;
        $form['user_id'] = $user->id;


        $item = Item::create($form);

        return back()->with('success', '出品が完了しました。');
    }

    public function favorite(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('show.login');
        }

        $user = Auth::user();
        $item = Item::findOrFail($id);

        if ($item->isFavoritedBy($user)) {
            $user->favorites()->detach($item->id);
        } else {
            $user->favorites()->attach($item->id);
        }

        return back();
    }

    public function showComment($item_id)
    {
        $item = Item::with('comments.user')->findOrFail($item_id);
        return view ('comment', compact('item_id', 'item'));
    }

    public function create (Request $request, $item_id)
    {
        $user = Auth::user();

        Comment::create([
            'user_id' => $user->id,
            'item_id' => $item_id,
            'content' => $request->input('content'),
        ]);

        return back()->with('success', 'コメントが追加されました。');
    }

    public function destroy (Request $request, $item_id, $comment_id)
    {
        $comment = Comment::where('id', $comment_id)->first();
        if ($comment) {
            $comment->delete();
        }
        return redirect()->back()->with('success', 'コメントを削除しました。');
    }

    public function showPurchaseForm ($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        return view ('purchase', [
            'item' => $item,
            'post' => $user->post,
            'address' => $user->address,
            'building' => $user->building,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('search-box');

        // 検索クエリを実行
        $searchResults = Item::where('name', 'like', '%' . $keyword . '%')
            ->orWhere('description', 'like', '%' . $keyword . '%')
            ->orWhere('brand', 'like', '%' . $keyword . '%')
            ->get();

        // セッションに検索結果とキーワードを保存
        session()->flash('searchResults', $searchResults);
        session()->flash('keyword', $keyword);

        return redirect()->route('index');
    }

}
