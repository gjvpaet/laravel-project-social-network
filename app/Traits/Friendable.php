<?php

namespace App\Traits;

use App\Friendship;
use App\User;

trait Friendable {
    public function add_friend($userRequestedId) {
        if ($this->id === $userRequestedId) {
            return 0;
        }

        if ($this->is_friends_with($userRequestedId) === 1) {
            return 'already friends';
        }

        if ($this->has_pending_friend_request_sent_to($userRequestedId) === 1) {
            return 'already sent a friend request.';
        }

        if ($this->has_pending_friend_requests_from($userRequestedId) === 1) {
            return $this->accept_friend($userRequestedId);
        }

        $friendship = Friendship::create([
            'requester' => $this->id,
            'user_requested' => $userRequestedId
        ]);

        if ($friendship) {
            return 1;
        } else {
            return 0;
        }
    }

    public function accept_friend($requester) {
        if ($this->has_pending_friend_requests_from($requester) === 0) {
            return 0;
        }

        $friendship = Friendship::where('requester', $requester)
                                ->where('user_requested', $this->id)
                                ->first();

        if ($friendship) {
            $friendship->update([
                'status' => 1
            ]);
            return 1;
        } else {
            return 0;
        }
    }

    public function friends() {
        $friends = [];

        $f1 = Friendship::where('status', 1)
                        ->where('requester', $this->id)
                        ->get();

        foreach ($f1 as $friendship) {
            array_push($friends, User::find($friendship->user_requested));
        }

        $friends2 = [];

        $f2 = Friendship::where('status', 1)
                        ->where('user_requested', $this->id)
                        ->get();

        foreach ($f2 as $friendship) {
            array_push($friends2, User::find($friendship->requester));
        }

        return array_merge($friends, $friends2);
    }

    public function pending_friend_requests() {
        $users = [];

        $friendships = Friendship::where('status', 0)
                            ->where('user_requested', $this->id)
                            ->get();

        foreach ($friendships as $friendship) {
            array_push($users, User::find($friendship->requester));
        }

        return $users;
    }

    public function friend_ids() {
        return collect($this->friends())->pluck('id');
    }

    public function is_friends_with($user_id) {
        if (in_array($user_id, $this->friend_ids()->toArray())) {
            return 1;
        } else {
            return 0;
        }
    }

    public function pending_friend_requests_ids() {
        return collect($this->pending_friend_requests())->pluck('id')->toArray();
    }

    public function pending_friend_requests_sent() {
        $users = [];

        $friendships = Friendship::where('status', 0)
                            ->where('requester', $this->id)
                            ->get();

        foreach ($friendships as $friendship) {
            array_push($users, User::find($friendship->user_requested));
        }

        return $users;
    }

    public function pending_friend_requests_sent_ids() {
        return collect($this->pending_friend_requests_sent())->pluck('id')->toArray();
    }

    public function has_pending_friend_requests_from($user_id) {
        if (in_array($user_id, $this->pending_friend_requests_ids())) {
            return 1;
        } else {
            return 0;
        }
    }

    public function has_pending_friend_request_sent_to($user_id) {
        if (in_array($user_id, $this->pending_friend_requests_sent_ids())) {
            return 1;
        } else {
            return 0;
        }
    }
}