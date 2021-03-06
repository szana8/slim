<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ParticipateInIssueTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function unauthenticated_users_may_not_add_replies()
    {
        $this->post('/issues/categories/1/replies', [])
            ->assertRedirect('login');
    }

    /** @test */
    function an_authenticated_user_may_participate_in_issues()
    {
        $this->signIn();

        $issue = create('App\Issue');
        $reply = factory('App\Reply')->make(['issue_id' => $issue->id]);

        $this->post($issue->path() . '/replies', $reply->toArray());

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
        $this->assertEquals(1, $issue->fresh()->replies_count);
    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $this->signIn();

        $issue = create('App\Issue');
        $reply = make('App\Reply', ['issue_id' => $issue->id, 'body' => null]);

        $this->json('post', $issue->path() . '/replies', $reply->toArray())
            ->assertStatus(422);
    }

    /** @test */
    function unauthorized_user_cannot_delete_replies()
    {
        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn();

        $this->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    function authorized_user_can_delete_replies()
    {
        $this->signIn();
        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}")->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, $reply->issue->fresh()->replies_count);
    }

    /** @test */
    function authorized_users_can_updates_replies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->patch("/replies/{$reply->id}", ['body' => 'FooBar']);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => 'FooBar']);
    }

    /** @test */
    function unauthorized_user_cannot_update_replies()
    {
        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}")->assertRedirect('login');

        $this->signIn();

        $this->patch("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    function replies_that_contain_spam_may_not_be_created()
    {
        $this->signIn();

        $issue = create('App\Issue');

        $reply = create('App\Reply', [
            'issue_id' => $issue->id,
            'body' => 'Yahoo Customer Support'
        ]);

        $this->json('post', $issue->path() . '/replies', $reply->toArray())
            ->assertStatus(422);
    }

    /** @test */
    function users_may_only_reply_a_maximum_of_once_per_minute()
    {
        $this->signIn();

        $issue = create('App\Issue');

        $reply = create('App\Reply', [
            'issue_id' => $issue->id,
            'body' => 'Simple reply'
        ]);

        $this->post($issue->path() . '/replies', $reply->toArray())
            ->assertStatus(201);

        $this->post($issue->path() . '/replies', $reply->toArray())
            ->assertStatus(429);
    }
}
