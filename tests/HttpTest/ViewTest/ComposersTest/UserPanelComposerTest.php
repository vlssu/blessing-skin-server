<?php

namespace Tests;

use Event;
use App\Models\User;

class UserPanelComposerTest extends TestCase
{
    public function testRenderUser()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $this->get('/user')
            ->assertSee(trans('admin.users.status.normal'))
            ->assertSee(
                url('avatar/45/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
    }

    public function testBadges()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        Event::listen(\App\Events\RenderingBadges::class, function ($event) {
            $event->badges[] = ['Pro', 'purple'];
        });

        $this->get('/user')->assertSee('<small class="label bg-purple">Pro</small>');
    }
}