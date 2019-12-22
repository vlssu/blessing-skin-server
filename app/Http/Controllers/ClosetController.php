<?php

namespace App\Http\Controllers;

use App\Models\Texture;
use App\Models\User;
use App\Services\Filter;
use Auth;
use Illuminate\Http\Request;

class ClosetController extends Controller
{
    public function index(Filter $filter)
    {
        $grid = [
            'layout' => [
                ['md-8', 'md-4'],
            ],
            'widgets' => [
                [
                    [
                        'user.widgets.email-verification',
                        'user.widgets.closet.list',
                    ],
                    ['shared.previewer'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:user.closet', $grid);

        return view('user.closet')
            ->with('grid', $grid)
            ->with('extra', [
                'unverified' => option('require_verification') && !auth()->user()->verified,
                'rule' => trans('user.player.player-name-rule.'.option('player_name_rule')),
                'length' => trans(
                    'user.player.player-name-length',
                    ['min' => option('player_name_length_min'), 'max' => option('player_name_length_max')]
                ),
            ]);
    }

    public function getClosetData(Request $request)
    {
        $category = $request->input('category', 'skin');
        $page = abs($request->input('page', 1));
        $perPage = (int) $request->input('perPage', 6);
        $q = $request->input('q', null);

        $perPage = $perPage > 0 ? $perPage : 6;

        $user = auth()->user();
        $closet = $user->closet();

        if ($category == 'cape') {
            $closet = $closet->where('type', 'cape');
        } else {
            $closet = $closet->where(function ($query) {
                return $query->where('type', 'steve')->orWhere('type', 'alex');
            });
        }

        if ($q) {
            $closet = $closet->where('item_name', 'like', "%$q%");
        }

        $total = $closet->count();
        $closet->offset(($page - 1) * $perPage)->limit($perPage);

        $totalPages = ceil($total / $perPage);
        $items = $closet->get()->map(function ($t) {
            $t->name = $t->pivot->item_name;

            return $t;
        });

        return json('', 0, [
            'category' => $category,
            'items' => $items,
            'total_pages' => $totalPages,
        ]);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
            'name' => 'required',
        ]);

        $user = Auth::user();

        if ($user->score < option('score_per_closet_item')) {
            return json(trans('user.closet.add.lack-score'), 7);
        }

        $tid = $request->tid;
        $texture = Texture::find($tid);
        if (!$texture) {
            return json(trans('user.closet.add.not-found'), 1);
        }

        if (!$texture->public && $texture->uploader != $user->uid) {
            return json(trans('skinlib.show.private'), 1);
        }

        if ($user->closet()->where('tid', $request->tid)->count() > 0) {
            return json(trans('user.closet.add.repeated'), 1);
        }

        $user->closet()->attach($tid, ['item_name' => $request->name]);
        $user->score -= option('score_per_closet_item');
        $user->save();

        $texture->likes++;
        $texture->save();

        $uploader = User::find($texture->uploader);
        if ($uploader && $uploader->uid != $user->uid) {
            $uploader->score += option('score_award_per_like', 0);
            $uploader->save();
        }

        return json(trans('user.closet.add.success', ['name' => $request->input('name')]), 0);
    }

    public function rename(Request $request, $tid)
    {
        $this->validate($request, ['name' => 'required']);
        $user = auth()->user();

        if ($user->closet()->where('tid', $request->tid)->count() == 0) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $user->closet()->updateExistingPivot($request->tid, ['item_name' => $request->name]);

        return json(trans('user.closet.rename.success', ['name' => $request->name]), 0);
    }

    public function remove($tid)
    {
        $user = auth()->user();

        if ($user->closet()->where('tid', $tid)->count() == 0) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $user->closet()->detach($tid);

        if (option('return_score')) {
            $user->score += option('score_per_closet_item');
            $user->save();
        }

        $texture = Texture::find($tid);
        $texture->likes--;
        $texture->save();

        $uploader = User::find($texture->uploader);
        $uploader->score -= option('score_award_per_like', 0);
        $uploader->save();

        return json(trans('user.closet.remove.success'), 0);
    }
}
